<?php

namespace App\Enums;

enum PermissionTypesEnum : string
{
    case READ_KEYS = 'read_keys';
    case CREATE_KEYS = 'create_keys';
    case EDIT_KEYS = 'edit_keys';
    case DELETE_KEYS = 'delete_keys';
    case READ_QUESTIONS = 'read_questions';
    case CREATE_QUESTIONS = 'create_questions';
    case IMPORT_QUESTIONS = 'import_questions';
    case EDIT_QUESTIONS = 'edit_questions';
    case DELETE_QUESTIONS = 'delete_questions';
    case READ_WIDGET_EXPORT = 'read_widget_export';

    case READ_DOCUMENTS = 'read_documents';
    case UPLOAD_DOCUMENTS = 'upload_documents';
    case DOWNLOAD_DOCUMENTS = 'download_documents';
    case DOWNLOAD_DOCUMENTS_FOLDER = 'download_documents_folder';
    case DELETE_DOCUMENTS = 'delete_documents';


    case CLOSE_CHAT = 'close_chat';

    public function getLabel(): string
    {
        return match ($this) {
            PermissionTypesEnum::READ_KEYS => __('enum.read_keys'),
            PermissionTypesEnum::CREATE_KEYS => __('enum.create_keys'),
            PermissionTypesEnum::EDIT_KEYS => __('enum.update_keys'),
            PermissionTypesEnum::DELETE_KEYS => __('enum.delete_keys'),
            PermissionTypesEnum::READ_QUESTIONS => __('enum.read_questions'),
            PermissionTypesEnum::CREATE_QUESTIONS => __('enum.create_questions'),
            PermissionTypesEnum::EDIT_QUESTIONS => __('enum.update_questions'),
            PermissionTypesEnum::DELETE_QUESTIONS => __('enum.delete_questions'),
            PermissionTypesEnum::READ_WIDGET_EXPORT => __('enum.read_widget_export'),
            PermissionTypesEnum::IMPORT_QUESTIONS => __('enum.import_questions'),
            PermissionTypesEnum::READ_DOCUMENTS => __('enum.read_documents'),
            PermissionTypesEnum::UPLOAD_DOCUMENTS => __('enum.upload_documents'),
            PermissionTypesEnum::DOWNLOAD_DOCUMENTS => __('enum.download_documents'),
            PermissionTypesEnum::DOWNLOAD_DOCUMENTS_FOLDER => __('enum.download_documents_folder'),
            PermissionTypesEnum::DELETE_DOCUMENTS => __('enum.delete_documents'),
            PermissionTypesEnum::CLOSE_CHAT => __('enum.close_chat'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn ($case) => [$case->value => $case->getLabel()]
        )->toArray();
    }
}
