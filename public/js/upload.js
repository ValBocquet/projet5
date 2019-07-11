$(document).ready(function () {
    let form = $('#form_form');
    let progress = $('#progress');
    let bar = $(progress).find(('.progress-bar'));


    let form_action = form[0].action;


    $(form).on('submit', function (e) {


        if($(form).find("#form_NameFile").val()) {
            var data = new FormData();
            data.append("file", $('#form_NameFile').get(0).files[0]);
            console.log($('#form_NameFile').get(0).files[0])

            progress.show();

            var config = {
                onUploadProgress: function(e) {
                    let percentCompleted = Math.round((e.loaded * 100) / e.total);
                    if(percentCompleted < 100) {
                        $(bar).text(percentCompleted+ ' %').width(percentCompleted+ '%');
                    } else {
                        $(bar).text('Done').width('100%');
                    }
                },
                headers: {'X-Requested-With': 'XMLHttpRequest'},
            };

        }

        axios.post(form_action, data, config).then(function (response) {
            console.log(response);
        })
            .catch(function (error) {
                console.log(error.response)
            });

    });
});
