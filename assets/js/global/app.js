// sweet alert notifications
function success_alert(msg) {
    return swal({
        title: "Success",
        text: msg,
        type: "Success",
        icon: 'success',
        dangerMode: true,
        timer: 15000,
        showConfirmButton: false
    });
}


// sweet alert notifications
function error_alert(msg) {
    return swal({
        title: "Error !!!",
        text: msg,
        type: "Error",
        icon: 'warning',
        dangerMode: true,
        timer: 15000,
        showConfirmButton: false
    });
}