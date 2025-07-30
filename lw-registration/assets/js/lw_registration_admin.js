jQuery(document).ready(function () {
  jQuery(".multiple-select").select2();
  jQuery(".panel-collapse").on("show.bs.collapse", function () {
    jQuery(this).siblings(".panel-heading").addClass("active");
  });

  jQuery(".panel-collapse").on("hide.bs.collapse", function () {
    jQuery(this).siblings(".panel-heading").removeClass("active");
  });
});

function LW_show_loader() {
  jQuery("#lw_loader_info_data").hide(0);
  jQuery("#lw_loader_data").show(0);
  jQuery("#lw_loader").fadeIn(300);
}

function LW_hide_loader() {
  jQuery("#lw_loader").hide(0);
}

function LW_show_loader_output(text, status) {
  var fa = '<i class="fa fa-times"></i>';
  var Popup = "LwFailedPopup";
  if (status == 1) {
    var fa = '<i class="fa fa-check"></i> ';
    Popup = "LwSuccessPopup";
  }

  jQuery("#lw_loader_data").hide(0);
  jQuery("#lw_loader_info_data").html(
    "<div class=" +
      Popup +
      ">" +
      fa +
      '<div id="lw_loader_info_data_text">' +
      text +
      "<div></div>"
  );
  jQuery("#lw_loader_info_data").fadeIn(300);
}

function LW_redirect_to(url) {
  setTimeout(function () {
    window.location.href = url;
  }, 3000);
}

jQuery(document).ready(function () {
  jQuery("#invitation_table").DataTable();
  jQuery("body").append(
    '<div id="lw_loader" style="display:none"><div id="lw_loader_container"> <div id="lw_loader_container_inner"> <div id="lw_loader_data"><div id="DM_css_loader"></div><div id="lw_css_loader_text">>We&apos;re working on it... </div></div><div id="lw_loader_info_data" style="display:none"></div></div></div></div>'
  );

  jQuery(document).on("submit", "#LwInvitationForm", function (event) {
    event.preventDefault();
    if (jQuery("#LwInvitationForm").valid()) {
      LW_show_loader();
      jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: lw_registration.ajaxUrl,
        data: {
          action: "lw_invitation_admin_action",
          data: jQuery("#LwInvitationForm").serialize(),
        },
        error: function (jqXHR, exception) {
          //window.location.reload();
        },
        success: function (resp) {
          if (resp.status == "1") {
            LW_show_loader_output(resp.message, 1);
            setTimeout(function () {
              LW_hide_loader();
              window.location.reload();
            }, 3000);
          } else {
            LW_show_loader_output(resp.message, 0);
            setTimeout(function () {
              LW_hide_loader();
            }, 3000);
          }
        },
      });
    }
  });
});
jQuery(document).on("click", ".resend_invitation", function (event) {
  var id = jQuery(this).attr("data-id");
  if (confirm("Are you sure you want to resend invitation?")) {
    LW_show_loader();
    jQuery.ajax({
      type: "POST",
      dataType: "json",
      url: lw_registration.ajaxUrl,
      data: {
        action: "lw_invitation_admin_resend",
        id: id,
      },
      error: function (jqXHR, exception) {
        //window.location.reload();
      },
      success: function (resp) {
        if (resp.status == "1") {
          LW_show_loader_output(resp.message, 1);
          setTimeout(function () {
            LW_hide_loader();
            window.location.reload();
          }, 3000);
        } else {
          LW_show_loader_output(resp.message, 0);
          setTimeout(function () {
            LW_hide_loader();
          }, 3000);
        }
      },
    });
  }
});
