
    <div class="btn-group" role="group" aria-label="@lang('labels.backend.access.users.user_actions')">
        <a href="{{ route('admin.clientes.ver', $no_membresia) }}" data-toggle="tooltip" data-placement="top" title="@lang('buttons.general.crud.view')" class="btn btn-info">
            <i class="fas fa-eye"></i>
        </a>

        <a href="{{ route('admin.clientes.editar', $no_membresia) }}" data-toggle="tooltip" data-placement="top" title="@lang('buttons.general.crud.edit')" class="btn btn-primary">
            <i class="fas fa-edit"></i>
        </a>

        <div class="btn-group btn-group-sm" role="group">
            <button id="userActions" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Opciones
            </button>
            <div class="dropdown-menu" aria-labelledby="userActions">
                    <a href="{{ route('admin.clientes.eliminar', $no_membresia) }}"
                       data-method="delete"
                       data-trans-button-cancel="@lang('buttons.general.cancel')"
                       data-trans-button-confirm="@lang('buttons.general.crud.delete')"
                       data-trans-title="@lang('strings.backend.general.are_you_sure')"
                       class="dropdown-item">@lang('buttons.general.crud.delete')</a>
            </div>
        </div>
    </div>