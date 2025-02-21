<x-filament-panels::page>
  <form wire:submit.prevent="submit">
    {{ $this->form }}
  </form>

  <script>
    {!! $this->reportScripts !!}
  </script>
</x-filament-panels::page>
