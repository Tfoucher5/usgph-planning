window.traitement = function (value, qui) {
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

window.format_date = function (value, afficheHeure = false, texteSeul = false) {
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

window.get_date = function (value) {
  if (value != null) {
    return new Date(value.substring(0, 4), value.substring(5, 7) - 1, value.substring(8, 10));
  } else {
    return null;
  }
}

window.nl2br = function (str, is_xhtml) {
  if (typeof str === 'undefined' || str === null) {
    return '';
  }
  var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
  return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

window.format_currency = function (data, minimumFractionDigits = 2, maximumFractionDigits = 2) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: minimumFractionDigits,
    maximumFractionDigits: maximumFractionDigits
  }).format(data)
}

window.format_number = function (data, minimumFractionDigits = 2, maximumFractionDigits = 2) {
  return new Intl.NumberFormat('fr-FR', {
    style: 'decimal',
    minimumFractionDigits: minimumFractionDigits,
    maximumFractionDigits: maximumFractionDigits
  }).format(data)
}

window.round_number = function (data) {
  return Math.round((data + Number.EPSILON) * 100) / 100;
}

window.format_phone = function (data, link = false) {
  if (data == null || data.trim().length == 0) {
    return '-';
  }

  let phone = data.replace(/(.{2})(?=.)/g, "$1 ");

  let retour = link ?
    '<a href="tel:' + data + '">' + phone + '</a>' :
    phone;

  return retour;
}

window.format_siret = function (data) {
  if (data == null || data.trim().length == 0) {
    return '-';
  }

  let siret = data.substr(0, 9).replace(/(.{3})(?=.)/g, "$1 ") + ' ' + data.substr(-5);

  return siret;
}

window.format_email = function (data) {
  if (data == null) {
    return '-';
  }

  return '<a href="mailto:' + data + '">' + data + '</a>';
}

window.srip_tags = function (value) {
  return value.replace(/(<([^>]+)>)/gi, "");
}
