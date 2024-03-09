<div class="collapse navbar-collapse" id="navbar-collapse">
    <ul class="nav navbar-nav navbar-right">
        <!-- Notifications -->
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" id="notif" data-toggle="dropdown" role="button">
                <span class="label label-pill label-danger count" style="border-radius:10px;"></span> <span class="fa fa-bell" style="font-size:18px;"></span>
            </a>
            <ul class="dropdown-menu">
                <li class="header">NOTIFICATIONS</li>
                <li class="body">
                    <ul class="menu" id="mynotif">

                    </ul>
                </li>
                <li class="footer">
                    <a href="#">View All Notifications</a>
                </li>
            </ul>
        </li>
        <!-- #END# Notifications -->
        <!-- Tasks -->
        <li class="dropdown">
            <a href="javascript:void(0);" class="dropdown-toggle" id="msg" data-toggle="dropdown" role="button">
                <span class="label label-pill label-danger number" style="border-radius:10px;"></span> <span class="fa fa-envelope-o" style="font-size:18px;"></span>
            </a>
            <ul class="dropdown-menu">
                <li class="header">MESSAGES</li>
                <li class="body">
                    <ul class="menu" id="mymsg">

                    </ul>
                </li>
                <li class="footer">
                    <a href="javascript:void(0);">View All Messages</a>
                </li>
            </ul>
            <!-- /.dropdown-messages -->
        </li>
        <!-- #END# Messsages -->
        <!-- Tasks -->
        <li class="dropdown">
            <a href="javascript:void(0);" class="dropdown-toggle" id="msg" data-toggle="dropdown" role="button">
                <span class="label label-pill label-danger number" style="border-radius:10px;"></span> <span class="fa fa-user-plus" style="font-size:18px;"></span>
            </a>
            <ul class="dropdown-menu">
                <li class="header"> <?php echo "Welcome! " . ($contractor_name); ?></li>
                <li>
                    <a href="profile.php">
                        <i class="material-icons">person</i>My Profile
                    </a>
                </li>
                <li role="separator" class="divider"></li>
                <li>
                    <a href="logout.php">
                        <i class="material-icons">input</i>Sign Out
                    </a>
                </li>
            </ul>
            <!-- /.dropdown-messages -->
        </li>
        <!-- #END# Messsages --> 
    </ul>
</div>

<script>
    $(document).ready(function() {

        function load_unseen_notification(view = '') {
            $.ajax({
                url: "fetchnotifications.php",
                method: "POST",
                data: {
                    view: view
                },
                dataType: "json",
                success: function(data) {
                    $('#mynotif').html(data.notification);
                    if (data.unseen_notification > 0) {
                        $('.count').html(data.unseen_notification);
                    }
                }
            });
        }

        function load_unseen_message(read = '') {
            $.ajax({
                url: "ajax/notifications/fetchmessages",
                method: "POST",
                data: {
                    read: read
                },
                dataType: "json",
                success: function(data) {
                    $('#mymsg').html(data.message);
                    if (data.unseen_message > 0) {
                        $('.number').html(data.unseen_message);
                    }
                }
            });
        }
        //
        // load_unseen_notification();
        // load_unseen_message();

        // $(document).on('click', '#notif', function() {
        //     $('.count').html('');
        //     load_unseen_notification('yes');
        // });
        //
        // $(document).on('click', '#msg', function() {
        //     $('.number').html('');
        //     load_unseen_message('yes');
        // });
        //
        // setInterval(function() {
        //     load_unseen_notification();
        //     load_unseen_message();
        // }, 5000);

    });
</script>