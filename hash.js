$(document).ready(function(){
    var url = $(location).attr("href").substring($(location).attr("href").lastIndexOf('=') + 1);
    console.log(url);
    
    if(url == "create" || url == "change" || url == "show") {
        $("#links").hide();
        $(".username").show();
        $(".submit").show();
        $(".back").show();
    }
    
    if(url == "create") {
       $(".pass_new").show();
       $(".pass_confirm").show();
       $(".submit").html("Create User");
       $(".submit").on("click", function(evt) {
            evt.preventDefault();
            var payload = {
                method: "POST",
                url: "api/create",
                data: {
                    "username": $(".username").val(),
                    "pass_new": $(".pass_new").val(),
                    "pass_confirm": $(".pass_confirm").val()
                }
            }
            submit(payload);
            return false;
        });
    } else if(url == "change") {
       $(".pass").show();
       $(".pass_new").show();
       $(".pass_confirm").show();
       $(".submit").html("Change Pwd");
       $(".submit").on("click", function(evt) {
            evt.preventDefault();
            var payload = {
                method: "POST",
                url: "api/change",
                data: {
                    "username": $(".username").val(),
                    "pass": $(".pass").val(),
                    "pass_new": $(".pass_new").val(),
                    "pass_confirm": $(".pass_confirm").val()
                }
            }
            submit(payload);
            return false;
        });
    } else if(url == "show") {
       $(".show").show();
       $(".pass").show();
       $(".submit").html("Show Users");
       $(".submit").on("click", function(evt) {
            evt.preventDefault();
            var payload = {
                method: "POST",
                url: "api/show",
                data: {
                    "username": $(".username").val(),
                    "pass": $(".pass").val()
                }
            }
            submit(payload);
            return false;
        });
    }
    
    //Submits to AJAX inputted data (based on button click)
    var submit = function(payload) {
        $.ajax({
            method: payload['method'],
            url: payload['url'],
            data: payload['data'],
            success: function(data) {
                console.log(data);
                if(data['error']) $(".echo").html(data['error']);
                else if(data['type']=="show") {
                    $("#input").hide();
                    $(".string").show();
                    $(".string").html(data['string']);
                } else if(data['type']=="change"||data['type']=="create") {
                    $(".echo").html(data['string']);
                }
            }
        });
    }
});