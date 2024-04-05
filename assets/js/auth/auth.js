$(function () {
    $('#submit_otp').on('click', (e) => {
        e.preventDefault();
        if (!$('#otp_code').val()) {
            $('#otp_code').next().text('field required');
            return;
        } else {
            $('#otp_code').next().text('');
        }
        $('#otp_form').submit();
    });


    $('#resend-btn').on('click', (e) => {
        e.preventDefault();
        $('#resend-form').submit();
    })
})

$(function () {
    $('#submit-btn').on('click', (e) => {
        e.preventDefault();
        if (!$('#email').val()) {
            $('#email').next().text('field required');
            return;
        } else {
            $('#email').next().text('');
        }
        $('#loginusers').submit();
    })
})


$(function () {
    $('#submit-btn').on('click', (e) => {
        e.preventDefault();

        if (!$('#email').val()) {
            $('#email').next().text('field required');
            return;
        } else {
            $('#email').next().text('');
        }

        if (!$('#password').val()) {
            $('#password').next().text('field required');
            return;
        } else {
            $('#password').next().text('');
        }

        if (!$('#confirm_password').val()) {
            $('#confirm_password').next().text('field required');
            return;
        } else {
            $('#confirm_password').next().text('');
        }

        $('#loginusers').submit();
    })
})