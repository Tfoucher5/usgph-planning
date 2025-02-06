<script>
  function traitement(value, qui) {
    if (value != null && qui != null) {
      var date = new Date(value.substring(0, 4), value.substring(5, 7) - 1, value.substring(8, 10), value.substring(11, 13), value.substring(14, 16), value.substring(17, 19));
      var options = {
        year: "numeric",
        month: "long",
        day: "numeric"
      };
      return '<span class="d-none">' + value + '</span><span class="small">' + date.toLocaleDateString("fr-FR", options) + ' à ' + date.toLocaleTimeString("fr-FR") + '<br/>par ' +
        qui + '</span>';
    } else {
      return '-';
    }
  }

  function format_date(value, afficheHeure = false, texteSeul = false) {
    if (value != null) {
      var date = afficheHeure ?
        new Date(value.substring(0, 4), value.substring(5, 7) - 1, value.substring(8, 10), value.substring(11, 13), value.substring(14, 16), value.substring(17, 19)) :
        new Date(value.substring(0, 4), value.substring(5, 7) - 1, value.substring(8, 10));
      var options = {
        year: "numeric",
        month: "long",
        day: "numeric"
      };
      return texteSeul ?
        date.toLocaleDateString("fr-FR", options) :
        '<span class="d-none">' + value + '</span><span class="small">' + date.toLocaleDateString("fr-FR", options) + '</span>';
    } else {
      return '<span class="d-none">2100-12-31</span>-';
    }
  }

  function get_date(value) {
    if (value != null) {
      return new Date(value.substring(0, 4), value.substring(5, 7) - 1, value.substring(8, 10));
    } else {
      return null;
    }
  }

  function nl2br(str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
      return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
  }

  function format_currency(data, minimumFractionDigits = 2, maximumFractionDigits = 2) {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: minimumFractionDigits,
      maximumFractionDigits: maximumFractionDigits
    }).format(data)
  }

  function format_number(data, minimumFractionDigits = 2, maximumFractionDigits = 2) {
    return new Intl.NumberFormat('fr-FR', {
      style: 'decimal',
      minimumFractionDigits: minimumFractionDigits,
      maximumFractionDigits: maximumFractionDigits
    }).format(data)
  }

  function round_number(data) {
    return Math.round((data + Number.EPSILON) * 100) / 100;
  }

  function format_phone(data, link = false) {
    if (data == null || data.trim().length == 0) {
      return '-';
    }

    let phone = data.replace(/(.{2})(?=.)/g, "$1 ");

    let retour = link ?
      '<a href="tel:' + data + '">' + phone + '</a>' :
      phone;

    return retour;
  }

  function format_siret(data) {
    if (data == null || data.trim().length == 0) {
      return '-';
    }

    let siret = data.substr(0, 9).replace(/(.{3})(?=.)/g, "$1 ") + ' ' + data.substr(-5);

    return siret;
  }

  function format_email(data) {
    if (data == null) {
      return '-';
    }

    return '<a href="mailto:' + data + '">' + data + '</a>';
  }

  function srip_tags(value) {
    return value.replace(/(<([^>]+)>)/gi, "");
  }

  function suppressionItem(item, id) {
    event.preventDefault();
    return (
      Swal.fire({
        title: '{{ __('Do you confirm the deletion of the line') }} [' + item + '] ?',
        icon: 'question',
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: true,
        confirmButtonText: '{{ __('Yes') }}',
        cancelButtonText: '{{ __('No') }}',
      }).then((result) => {
        if (result.value) {
          document.getElementById('form-delete-' + id).submit();
        } else {
          return false
        }
      })
    );
  }

  function restaurationItem(item, url) {
    event.preventDefault();
    return (
      Swal.fire({
        title: '{{ __('Do you confirm the restauration of the line') }} [' + item + '] ?',
        icon: 'question',
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: true,
        confirmButtonText: '{{ __('Yes') }}',
        cancelButtonText: '{{ __('No') }}',
      }).then((result) => {
        if (result.value) {
          window.location.href = url;
        } else {
          return false
        }
      })
    );
  }

  // if ($.fn.dataTable != undefined) {
  //   // Affiche les éléments supprimés si le curseur est positionné à oui
  //   $.fn.dataTable.ext.search.push(
  //     function(settings, data, dataIndex) {
  //       var afficher = $('#showDeleted').is(":checked");

  //       return afficher || data[col_deleted_at].length < 4;
  //     }
  //   );
  // }

  function setBootstrap5Style() {
    let divFilter = $('#table_id_filter');
    divFilter.removeClass('dataTables_filter');
    divFilter.addClass('form-floating');

    let labelFilter = $('#table_id_filter > label');
    let inputFilter = $('#table_id_filter > label > input');

    inputFilter.detach().appendTo(divFilter);
    labelFilter.detach().appendTo(divFilter);

    inputFilter.attr('placeholder', '{{ __('Search') }}');
    labelFilter.html('<i class="fa-solid fa-magnifying-glass me-1"></i>');

    let labelEntries = $('#table_id_length > label');
    labelEntries.addClass('d-flex align-items-center');

    let selectEntries = $('#table_id_length > label > select');
    selectEntries.addClass('w-auto mx-1');
  };

  function searchChanged(e) {
    Cookies.set('search_{{ session('level_menu_2') }}', e.currentTarget.value);
  }

  function inner(table, col_deleted_at) {
    // Associer l'événement d'affichage dynamique des lignes supprimées
    $('#showDeleted').change(function() {
      var column = table.column(col_deleted_at);
      column.visible(!column.visible());

      table.draw();
    });

    // Recherche en cours
    table.search(Cookies.get('search_{{ session('level_menu_2') }}'));
  }
</script>
