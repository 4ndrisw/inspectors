// Init single inspector
function init_inspector(userid) {
    load_small_table_item(userid, '#inspector', 'inspectorid', 'inspectors/get_inspector_data_ajax', '.table-inspectors');
}

// Validates inspector add/edit form
function validate_inspector_form(selector) {

    selector = typeof (selector) == 'undefined' ? '#inspector-form' : selector;

    appValidateForm($(selector), {
        clientid: {
            required: {
                depends: function () {
                    var customerRemoved = $('select#clientid').hasClass('customer-removed');
                    return !customerRemoved;
                }
            }
        },
        date: 'required',
        office_id: 'required',
        number: {
            required: true
        }
    });

    $("body").find('input[name="number"]').rules('add', {
        remote: {
            url: admin_url + "inspectors/validate_inspector_number",
            type: 'post',
            data: {
                number: function () {
                    return $('input[name="number"]').val();
                },
                isedit: function () {
                    return $('input[name="number"]').data('isedit');
                },
                original_number: function () {
                    return $('input[name="number"]').data('original-number');
                },
                date: function () {
                    return $('body').find('.inspector input[name="date"]').val();
                },
            }
        },
        messages: {
            remote: app.lang.inspector_number_exists,
        }
    });

}


// Staff projects table in staff profile
function init_table_inspector_staff_companies(manual) {
  if (typeof manual == "undefined" && $("body").hasClass("dashboard")) {
    return false;
  }
  if ($("body").find(".table-inspector-staff-companies").length === 0) {
    return;
  }

  var staffProjectsParams = {},
    Staff_Projects_Filters = $(
      "._hidden_inputs._filters.staff_companies_filter input"
    );

  $.each(Staff_Projects_Filters, function () {
    staffProjectsParams[$(this).attr("name")] =
      '[name="' + $(this).attr("name") + '"]';
  });

  initDataTable(
    ".table-inspector-staff-companies",
    admin_url + "inspectors/staff/inspector_staff_companies",
    "undefined",
    "undefined",
    staffProjectsParams,
    [2, "asc"]
  );
}


// Staff projects table in staff profile
function init_table_inspector_staff_projects(manual) {
  if (typeof manual == "undefined" && $("body").hasClass("dashboard")) {
    return false;
  }
  if ($("body").find(".table-inspector-staff-projects").length === 0) {
    return;
  }

  var staffProjectsParams = {},
    Staff_Projects_Filters = $(
      "._hidden_inputs._filters.staff_projects_filter input"
    );

  $.each(Staff_Projects_Filters, function () {
    staffProjectsParams[$(this).attr("name")] =
      '[name="' + $(this).attr("name") + '"]';
  });

  initDataTable(
    ".table-inspector-staff-projects",
    admin_url + "inspectors/staff/inspector_staff_projects",
    "undefined",
    "undefined",
    staffProjectsParams,
    [2, "asc"]
  );
}


// Get the preview main values
function get_inspector_item_preview_values() {
    var response = {};
    response.description = $('.main textarea[name="description"]').val();
    response.long_description = $('.main textarea[name="long_description"]').val();
    response.qty = $('.main input[name="quantity"]').val();
    return response;
}


// From inspector table mark as
function inspector_mark_as(state_id, inspector_id) {
    var data = {};
    data.state = state_id;
    data.inspectorid = inspector_id;
    $.post(admin_url + 'inspectors/update_inspector_state', data).done(function (response) {
        //table_inspectors.DataTable().ajax.reload(null, false);
        reload_inspectors_tables();
    });
}

// Reload all inspectors possible table where the table data needs to be refreshed after an action is performed on task.
function reload_inspectors_tables() {
    var av_inspectors_tables = ['.table-inspectors', '.table-rel-inspectors'];
    $.each(av_inspectors_tables, function (i, selector) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().ajax.reload(null, false);
        }
    });
}
