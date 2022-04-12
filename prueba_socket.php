<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Bootstrap 101 Template</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <style>
        #message_box {
            height: 200px;
            overflow: scroll;

        }
        .row {
            margin-bottom: 30px;
        }
    </style>

</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <h1>websocket sample</h1>
            <div>

                <div class="input-group">
                    <label for="name"></label><input type="text" class="form-control" name="name" id="name" placeholder="Your name">
                    <div class="input-group-btn">
                        <button id="connect" type="button" class="btn btn-primary">Connect</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row users-panel">
        <div class="col-lg-2">

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Connected users</h3>
                </div>
                <div class="panel-body">
                    <div id="users-box">
                    </div>
                </div>
            </div>


        </div>
        <div class="col-lg-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Private room</h3>
                </div>
                <div class="panel-body">
                    <div id="users-selected">
                        <ul class="list-unstyled"></ul>
                    </div>
                </div>
            </div>


        </div>
        <div class="col-lg-8">


            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Chat panel</h3>
                </div>
                <div class="panel-body">
                    <div class="panel panel-default" id="message_box"></div>
                    <form class="form-inline" id="form-message">
                        <div class="input-group">
                            <input type="text" class="form-control" name="message" id="message" placeholder="Text">
                            <div class="input-group-btn">
                                <button id="send-message" type="button" class="btn btn-primary">Send</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Invitation request</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" id="accept-invitation" class="btn btn-primary">Accept</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function(){

        // set some global vars
        var w = $(window);
        var user_sid = '<?php print session_id(); ?>';

        w.onbeforeunload = function() {
            w.websocket.onclose = function () {};
            w.websocket.close()
        };


        $('#connect').click(function(){

            if ($(this).hasClass('connected')) {
                if (is_connected(w.websocket)) {
                    w.websocket.onclose = function () {};
                    w.websocket.close();
                    $(this).removeClass('connected btn-default').addClass('btn-primary').html("Connect");
                    return;
                }
            }
            // set some vars
            w.name = $('#name').val();
            w.wsUri = "ws://localhost:9000/ciproteo/server.php";
            w.websocket = new WebSocket(w.wsUri);

            w.websocket.onopen = function(ev) {
                var msg = {
                    name : w.name,
                    sid : '<?php print session_id(); ?>'
                };
                w.websocket.send(JSON.stringify(msg));
                $('#message_box').prepend("<div class=\"system_msg\">Connected!</div>"); //notify user
                $('#connect').addClass('btn-default connected').removeClass('btn-primary').html('Disconnect');
            }

            w.websocket.onmessage = function(ev) {
                var msg    = JSON.parse(ev.data); //PHP sends Json data
                var type   = msg.type;
                var umsg   = msg.message; //message text
                var uname  = msg.name; //user name
                var ucolor = msg.color; //color
                var sid    = msg.sid; //session id

                if(type === 'invite') {

                    if (msg.invite === 1) {

                        $('.modal-body').html("<div><span class=\"user_message\">"+umsg+"</span></div>");
                        $('#modal').modal();
                        $('#accept-invitation').on('click', function(){
                            $('#users-list ul :checkbox').each(function(){
                                if (sid === $(this).attr('data-id')) {
                                    $(this).attr('checked', 'checked');
                                    $(this).closest('li').detach().prependTo('#users-selected ul');

                                    var to_users = [];
                                    to_users.push(sid);

                                    //prepare json data
                                    var msg = {
                                        message: w.name + " has accepted your invitation",
                                        name: w.name,
                                        //color : '<?php //echo $colours[$user_colour]; ?>',
                                        sid : '<?php print session_id(); ?>',
                                        to_users: to_users,
                                        invite: 2,
                                    };

                                    //convert and send data to server
                                    w.websocket.send(JSON.stringify(msg));
                                    $('#modal').modal('hide');
                                }
                            });
                        });
                    }

                    if (msg.invite === 2) {
                        $('.modal-body').html("<div><span class=\"user_message\">"+umsg+"</span></div>");
                        $("#accept-invitation").hide();
                        $('#modal').modal();
                    }
                }

                if(type === 'usermsg') {
                    $('#message_box').prepend("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");
                }
                if(type === 'system') {
                    $('#message_box').prepend("<div class=\"system_msg\">"+umsg+"</div>");
                }
                if(type === 'users') {
                    var list = "";
                    var users = $.parseJSON( umsg );
                    $(users).each(function(i, user) {
                        if (user.id !== user_sid) {
                            list += "<li class='user-list' data-name='"+user.name+"'><label><input type='checkbox' data-id='"+user.id+"'>"+user.name+"</label></li>";
                        }
                    });

                    $('#users-box').html("<div id='users-list' class=\"users\"><ul class='list-unstyled'>"+list+"</ul></div>");
                }

                $('#message').val(''); //reset text
            };

            w.websocket.onerror = function(ev){$('#message_box').append("<div class=\"system_error\">Error Occurred - "+ev.data+"</div>");};
            w.websocket.onclose   = function(ev){$('#message_box').append("<div class=\"system_msg\">Connection Closed</div>");};

        });


        $('#send-message').on('click', function(){
            var mymessage = $('#message').val();
            sendMessage(mymessage);
            $('#message').val("").focus();
        });


        $('form input').keydown(function(event){
            if(event.keyCode === 13) {
                event.preventDefault();
                var mymessage = $('#message').val();
                sendMessage(mymessage);
                $('#message').val("").focus();
                return false;
            }
        });


        $('.users-panel').delegate(':checkbox', 'click', function() {
            if (this.checked) {
                $(this).closest('li').detach().prependTo('#users-selected ul');
            } else {
                $(this).closest('li').detach().prependTo('#users-list ul');
            }
        });


        function is_connected(socket) {
            if (socket['readyState'] === 1) {
                return true;
            }
        }


        function sendMessage(mymessage){

            var to_users = [];
            $('input:checked').each(function(){
                to_users.push( $(this).attr('data-id') );
            });

            if(mymessage === ""){ //emtpy message?
                alert("Enter Some message Please!");
                return;
            }

            //prepare json data
            var msg = {
                message: mymessage,
                name: w.name,
                //color : '<?php //echo $colours[$user_colour]; ?>',
                sid : '<?php print session_id(); ?>',
                to_users: to_users
            };

            //convert and send data to server
            w.websocket.send(JSON.stringify(msg));
        }


        $('.users-panel').delegate(':checkbox', 'click', function() {
            if (this.checked) {
                //$(this).closest('li').detach().prependTo('#users-selected ul');

                $('.modal-body').html("Waiting for a response...");
                $('#modal').modal();


                var to_users = [];
                to_users.push($(this).attr('data-id'));

                //prepare json data
                var msg = {
                    message: w.name + " has invited you to a private chat room",
                    name: w.name,
                    //color : '<?php //echo $colours[$user_colour]; ?>',
                    sid : '<?php print session_id(); ?>',
                    to_users: to_users,
                    invite: 1,
                };

                //convert and send data to server
                w.websocket.send(JSON.stringify(msg));

            }
        });
    });

</script>
</body>
</html>
