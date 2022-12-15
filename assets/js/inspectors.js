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



function init_assignment() {

  // On hidden modal assignment set all values to empty and set the form action to ADD in case edit was clicked
  $("body").on("hidden.bs.modal", ".modal-assignment", function (e) {
    var $this = $(this);
    var rel_id = $this.find('input[name="rel_id"]').val();
    var rel_type = $this.find('input[name="rel_type"]').val();
    $this
      .find("form")
      .attr(
        "action",
        admin_url + "inspectors/add_assignment/" + rel_id + "/" + rel_type
      );
    $this.find("form").removeAttr("data-edit");
    $this.find(":input:not([type=hidden]), textarea").val("");
    $this.find('input[type="checkbox"]').prop("checked", false);
    $this.find("select").selectpicker("val", "");
  });

  // Focus the date field on assignment modal shown
  $("body").on("shown.bs.modal", ".modal-assignment", function (e) {
    if ($(this).find('form[data-edit="true"]').length == 0) {
      $(this).find("#date").focus();
    }
  });

  // On delete assignment reload the tables
  $("body").on("click", ".delete-assignment", function () {
    if (confirm_delete()) {
      requestGetJSON($(this).attr("href")).done(function (response) {
        alert_float(response.alert_type, response.message);
        if ($("#task-modal").is(":visible")) {
          _task_append_html(response.taskHtml);
        }
        reload_assignments_tables();
      });
    }
    return false;
  });


  // Custom close function for assignment modals in case is modal in modal
  $("body").on("click", ".close-assignment-modal", function () {
    $(
      ".assignment-modal-" +
        $(this).data("rel-type") +
        "-" +
        $(this).data("rel-id")
    ).modal("hide");
  });

}

// Validate the form assignment
function init_form_assignment(rel_type) {
  var forms = !rel_type
    ? $('[id^="form-assignment-"]')
    : $("#form-assignment-" + rel_type);

  $.each(forms, function (i, form) {
    $(form).appFormValidator({
      rules: {
        date: "required",
        staff: "required",
        description: "required",
      },
      submitHandler: assignmentFormHandler,
    });
  });
}

// New task assignment custom function
function new_task_assignment(id) {
  var $container = $("#newTaskassignmentToggle");
  if (
    !$container.is(":visible") ||
    ($container.is(":visible") && $container.attr("data-edit") != undefined)
  ) {
    $container.slideDown(400, function () {
      fix_task_modal_left_col_height();
    });

    $("#taskassignmentFormSubmit").html(app.lang.create_assignment);
    $container
      .find("form")
      .attr("action", admin_url + "tasks/add_assignment/" + id);

    $container.find("#description").val("");
    $container.find("#date").val("");
    $container
      .find("#staff")
      .selectpicker(
        "val",
        $container.find("#staff").attr("data-current-staff")
      );
    $container.find("#notify_by_email").prop("checked", false);
    if ($container.attr("data-edit") != undefined) {
      $container.removeAttr("data-edit");
    }
    if (!$container.isInViewport()) {
      $("#task-modal").animate(
        {
          scrollTop: $container.offset().top + "px",
        },
        "fast"
      );
    }
  } else {
    $container.slideUp();
  }
}

// Edit assignment function
function edit_assignment(id, e) {
  requestGetJSON("inspectors/get_assignment/" + id).done(function (response) {
    var $container = $(
      ".assignment-modal-" + response.rel_type + "-" + response.rel_id
    );
    var actionURL = admin_url + "inspectors/edit_assignment/" + id;
    if ($container.length === 0 && $("body").hasClass("all-assignments")) {
      // maybe from view all assignments?
      $container = $(".assignment-modal--");
      $container.find('input[name="rel_type"]').val(response.rel_type);
      $container.find('input[name="rel_id"]').val(response.rel_id);
    }
    $container.find("form").attr("action", actionURL);
    // For focusing the date field
    $container.find("form").attr("data-edit", true);
    $container.find("#description").val(response.description);
    //$container.find("#date").val(response.date);
    $container.find("#date_issued").val(response.date_issued);
    $container.find("#date_expired").val(response.date_expired);
    $container.find("#assignment_number").val(response.assignment_number);
    $container.find("#staff").selectpicker("val", response.staff);
    $container.find("#category_id").selectpicker("val", response.category_id);
    $container
      .find("#notify_by_email")
      .prop("checked", response.notify_by_email == 1 ? true : false);
    if ($container.hasClass("modal")) {
      $container.modal("show");
    }
  });
}

// Handles assignment modal form
function assignmentFormHandler(form) {
  form = $(form);
  var data = form.serialize();
  $.post(form.attr("action"), data).done(function (data) {
    data = JSON.parse(data);
    if (data.message !== "") {
      alert_float(data.alert_type, data.message);
    }
    form.trigger("reinitialize.areYouSure");
    if ($("#task-modal").is(":visible")) {
      _task_append_html(data.taskHtml);
    }
    reload_assignments_tables();
  });

  if ($("body").hasClass("all-assignments")) {
    $(".assignment-modal--").modal("hide");
  } else {
    $(
      ".assignment-modal-" +
        form.find('[name="rel_type"]').val() +
        "-" +
        form.find('[name="rel_id"]').val()
    ).modal("hide");
  }

  return false;
}

// Reloads assignments table eq when assignment is deleted
function reload_assignments_tables() {
  var available_assignments_table = [
    ".table-assignments",
    ".table-staff_assignments",
    ".table-my-assignments",
  ];

  $.each(available_assignments_table, function (i, table) {
    if ($.fn.DataTable.isDataTable(table)) {
      $("body").find(table).DataTable().ajax.reload();
    }
  });
}