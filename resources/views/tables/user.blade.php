@if (isset($data) && $data->isNotEmpty())
    <x-ui.base-datatable
        :headings="['#',  'Name',   'Email', 'Edit', 'Delete']"
        :values="[
            [
                'key' => 'id',
                'type' => 'data'
            ],

            [
                'key' => 'name',
                'type' => 'data'
            ],
            [
                'key' => 'email',
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
        edit-route="user.edit"
        delete-route="user.delete"
        edit-id="id"
        delete-id="id"
        model="user"
        table-striped
        :orderable="false"
    >
    </x-ui.base-datatable>
@else
    No categories found.
@endif
