<?php

namespace App\Livewire;

use App\Actions\UploadFileAction;
use App\Enums\FileStatusEnum;
use App\Enums\PermissionTypesEnum;
use App\Jobs\ProcessDocumentJob;
use App\Livewire\Traits\FileHandlerTrait;
use App\Livewire\Traits\GlobalNotifyEvent;
use App\Models\Document;
use App\Models\Site;
use App\Models\SiteSelector;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class DocumentManager extends Component
{
    use FileHandlerTrait;
    use WithFileUploads;
    use WithPagination;
    use WithoutUrlPagination;
    use GlobalNotifyEvent;

    #[Validate('required|file|max:200240|mimes:pdf,doc,docx,txt,html,md')]
    public $file;
    #[Validate('required|string|max:255')]
    public string $title;
    #[Validate('required|integer|exists:sites,id')]
    public ?int $site_id = null;

    public string $sort_by = 'title';
    public string $sort_direction = 'asc';
    public ?string $search = null;

    public ?Site $site = null;
    public $debugInfo = [];

    public ?Document $selected_document = null;

    public function mount(SiteSelector $site_selector): void
    {
        if (!$site_selector->hasSite()) {
            redirect()->route('site.picker')->with('error', __('interface.missing_site'));
        }

        $this->site = $site_selector->getSite();

        $this->site_id = $this->site?->id ?? null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function download(int $id): ?StreamedResponse
    {
        if (auth()->user()->cannot('hasPermission', PermissionTypesEnum::DOWNLOAD_DOCUMENTS)) {
            $this->dispatch('notify', 'danger', __('interface.missing_permission'));
            return null;
        }

        $document = $this->site->documents()->find($id);

        if ($document) {
            return Storage::disk('public')->download($document->path, $document->title . '.' . $document->type->value);
        } else {
            $this->notify('warning', __('interface.document_not_found'));
            return null;
        }
    }

    public function downloadFolder(): ?BinaryFileResponse
    {
        if (auth()->user()->cannot('hasPermission', PermissionTypesEnum::DOWNLOAD_DOCUMENTS_FOLDER)) {
            $this->dispatch('notify', 'danger', __('interface.missing_permission'));
            return null;
        }

        $documents = $this->site->documents()->get();

        if ($documents->isEmpty()) {
            $this->notify('warning', __('interface.document_not_found'));
            return null;
        }

        $zip = new ZipArchive;
        $zipFileName = "site_{$this->site->name}_documents.zip";
        $zipFilePath = storage_path("app/temp/{$zipFileName}");

        if (!file_exists(dirname($zipFilePath))) {
            mkdir(dirname($zipFilePath), 0777, true);
        }

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($documents as $document) {
                $filePath = Storage::disk('public')->path($document->path);
                if (file_exists($filePath)) {
                    $relativeName = $document->title . '.' . $document->type->value;
                    $zip->addFile($filePath, $relativeName);
                }
            }
            $zip->close();
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function save(): void
    {
        if (auth()->user()->cannot('hasPermission', PermissionTypesEnum::UPLOAD_DOCUMENTS)) {
            $this->dispatch('notify', 'danger', __('interface.missing_permission'));
            return;
        }

        $this->validate();

        if ($path = (new UploadFileAction())->execute($this->site_id, $this->file)) {
            $document = Document::create([
                'site_id' => $this->site_id,
                'title' => $this->title,
                'path' => $path,
                'type' => $this->file->getClientOriginalExtension(),
                'status' => FileStatusEnum::UPLOADED,
            ]);

            ProcessDocumentJob::dispatch($this->site_id, $document);
            $this->notify('success', __('interface.document_saved'));
            $this->reset('file', 'title');
            Flux::modal('add-document')->close();
        } else {
            $this->notify('error', __('interface.'));
        }
    }

    public function delete(int $document_id): void
    {
        if (auth()->user()->cannot('hasPermission', PermissionTypesEnum::DELETE_DOCUMENTS)) {
            $this->dispatch('notify', 'danger', __('interface.missing_permission'));
            return;
        }

        $this->selected_document = $this->site->documents()->find($document_id);

        if (!$this->selected_document) {
            $this->notify('danger', __('interface.document_not_found'));
            return;
        }

        Flux::modal('delete-document')->show();
    }

    public function destroy(): void
    {
        if (empty($this->selected_document)) {
            $this->notify('error', __('interface.document_not_found'));
            return;
        }

        $this->selected_document->chunks()->delete();
        $this->selected_document->delete();

        $this->notify('success', __('interface.delete_success'));
        Flux::modal('delete-document')->close();
    }

    public function reloadDocuments(): void
    {
        $this->resetPage();
    }

    public function sort($column): void
    {
        if ($this->sort_by === $column) {
            $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort_by = $column;
            $this->sort_direction = 'asc';
        }
    }

    public function render()
    {
        $documents = $this->site->documents()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('type', 'like', '%' . $this->search . '%');
            })
            ->select('id', 'title', 'path', 'type', 'status')
            ->orderBy($this->sort_by, $this->sort_direction)
            ->paginate(10);

        return view('livewire.document-manager', [
            'documents' => $documents,
        ]);
    }
}
