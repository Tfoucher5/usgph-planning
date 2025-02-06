function inner(table, search) {
    // Associer l'événement d'affichage dynamique des lignes supprimées
    $('#showDeleted').change(function () {
        var column = table.column(col_deleted_at);
        column.visible(!column.visible());

        table.draw();
    });

    // Recherche en cours
    table.search(search);
}

function setBootstrap5Style() {
    let divFilter = $('#table_id_filter');
    divFilter.removeClass('dataTables_filter');
    divFilter.addClass('form-floating');

    let labelFilter = $('#table_id_filter > label');
    let inputFilter = $('#table_id_filter > label > input');

    inputFilter.detach().appendTo(divFilter);
    labelFilter.detach().appendTo(divFilter);

    // inputFilter.attr('placeholder', '{{ __('Search') }}');
    // labelFilter.html('<i class="fa-solid fa-magnifying-glass me-1"></i>');

    let labelEntries = $('#table_id_length > label');
    labelEntries.addClass('d-flex align-items-center');

    let selectEntries= $('#table_id_length > label > select');
    selectEntries.addClass('w-auto mx-1');
  };
