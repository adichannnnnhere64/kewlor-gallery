@if (isset($data) && $data->isNotEmpty())
    <x-ui.base-datatable
        :headings="['#', 'Image', 'Name', 'Date', 'Edit', 'Delete']"
        :values="[
            [
                'key' => 'id',
                'type' => 'data'
            ],
            [
                'key' => 'image',
                'type' => 'image'
            ],
            [
                'key' => 'name',
                'type' => 'data'
            ],
            [
                'key' => 'slug',
                'type' => 'data'
            ],

            [
                'key' => 'manage',
                'type' => 'edit'
            ],
            [
                'key' => 'manage',
                'type' => 'delete'
            ],
        ]"
        :data="$data"
        edit-route="category.edit"
        delete-route="category.delete"
        edit-id="id"
        delete-id="id"
        model="category"
        table-striped
    >
    </x-ui.base-datatable>
@else
    No categories found.
@endif
