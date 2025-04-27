@props([
    'options' => [],
    'wireModel' => '',
    'label' => '',
    'id' => 'tom-multiselect-' . uniqid(),
    'isMultiple' => false,
    'selected' => [], // Add this new prop for default values
])

<div wire:ignore>
    <div>
        @if ($label)
            <label class="block text-sm font-medium leading-5 text-gray-700 dark:text-gray-300" for="{{ $id }}">{{ $label }}</label>
        @endif
    </div>
    <select id="{{ $id }}" multiple  wire:model="{{ $wireModel }}" class="w-full border border-gray-300 rounded-md">
        @foreach($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    let tomSelect = new TomSelect("#{{ $id }}", {
      plugins: ['remove_button'],
      items: @json($selected)
    });

    tomSelect.on('change', function() {
        @this.set('{{ $wireModel }}', tomSelect.getValue());
    });

    Livewire.hook('message.processed', (message, component) => {
          console.log('burat')
        const selectElement = document.getElementById('{{ $id }}');
        if (selectElement) {
            const selectedValues = Array.from(selectElement.selectedOptions).map(option => option.value);
            tomSelect.setValue(selectedValues);
        }
    });
  });
</script>
