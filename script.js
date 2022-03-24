(function ($) {
  $(document).ready(function () {
    "use strict";

    $('#applyForSomeone1').click(function () {
      if ($(this).is(':checked'))
        $('.row.hidden').show();
    });

    $('#applyForSomeone2').click(function () {
      if ($(this).is(':checked'))
        $('.row.hidden').hide();
    });


    $('.add-asset').click(function () {
      var clone = $("#assets .form-group:first").clone();
      clone.find("input").val("");
      $(".buttonBox1").before(clone);
    });

    $('.add-liability').click(function () {
      var clone = $("#liabilities .form-group:first").clone();
      clone.find("input").val("");
      $(".buttonBox2").before(clone);
    });

  });

  $('#apply-now').steps({
    onFinish: function () {
      var data = new FormData();

      //Form data
      var form_data = $('#apply').serializeArray();
      console.log(form_data);
      $.each(form_data, function (key, input) {
        data.append(input.name, input.value);
      });

      //File data
      var docs_liability = $('input[name="documents_liability"]')[0].files;
      var docs_identification = $('input[name="documents_identification"]')[0].files;

      for (var i = 0; i < docs_liability.length; i++) {
        data.append("documents_liability[]", docs_liability[i]);
      }

      for (var i = 0; i < docs_identification.length; i++) {
        data.append("documents_identification[]", docs_identification[i]);
      }

      $('.loading').show();
      $.ajax({
        url: "php/sendmail.php",
        method: "post",
        processData: false,
        contentType: false,
        data: data,
        success: function (data) {
          var arr = JSON.parse(JSON.stringify(data));

          $('.page-title').hide();
          $('#apply').hide();
          $('.apply').scrollTop(0);
          $('.form-success .name').text(data.firstName);
          $('.form-success').show();
          $('.loading').hide();
        },
        error: function (e) {
          console.log('error');
          $('.loading').hide();
        }
      });
    } //end onFinish event
  });

})(jQuery);
