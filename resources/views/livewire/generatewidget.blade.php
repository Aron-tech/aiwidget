<?php

use Livewire\Volt\Component;
use App\Models\Site;

new class extends Component {

    public function mount(Site $site)
    {
        $this->site = $site;
    }

}; ?>

<div>

</div>
