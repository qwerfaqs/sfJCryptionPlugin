
<style type="text/css">
    html,body {
        margin:0;
        padding:0;
        font-family:Tahoma;
        font-size:12px;
    }
    input,textarea,select {
        font-family:Tahoma;
        font-size:12px;
    }
</style>

<p id="status"><span style="font-size: 16px;">Encrypting channel ...</span> <img src="/images/ajax-loader-msg1.gif" alt="Loading..." title="Loading..." style="margin-right:15px;" /></p>
String: <input type="text" id="text" disabled="disabled" /> <button id="encrypt" disabled="disabled">encrypt</button><button id="decrypt" disabled="disabled">decrypt</button><button id="serverChallenge" disabled="disabled">get encrypted time from server</button><br/>
Log:<br/>
<textarea cols="60" rows="25" id="log"></textarea>
<script type="text/javascript">
    $(document).ready(function(e){
        var $loader = $('<img src="/images/ajax-loader-msg1.gif" alt="Loading..." title="Loading..." style="margin-right:15px;" />');
        var hashObj = new jsSHA("mySuperPassword", "ASCII");
        var password = hashObj.getHash("SHA-512", "HEX");

        $.jCryption.authenticate(password, "<?php echo url_for('jcryption/generateKeyPair') ?>", "<?php echo url_for('jcryption/handShake') ?>", function(AESKey) {
            $("#text,#encrypt,#decrypt,#serverChallenge").attr("disabled",false);
            $("#status").html('<span style="font-size: 16px;">Let\'s Rock!</span>');
        }, function() {
            // Authentication failed
            alert('FAILED');
        });

        $("#encrypt").click(function() {
            var encryptedString = $.jCryption.encrypt($("#text").val(), password);
            $("#log").prepend("\n").prepend("----------");
            $("#log").prepend("\n").prepend("String: " + $("#text").val());
            $("#log").prepend("\n").prepend("Encrypted: " + encryptedString);
            $.ajax({
                url: "<?php echo url_for('jcryption/decrypt') ?>",
                dataType: "json",
                type: "POST",
                data: {
                    jCryption: encryptedString
                },
                success: function(response) {
                    $("#log").prepend("\n").prepend("Server decrypted: " + response.data);
                }
            });
        });

        $("#serverChallenge").click(function() {
            $.ajax({
                url: "<?php echo url_for('jcryption/decryptTest') ?>",
                dataType: "json",
                type: "POST",
                success: function(response) {
                    $("#log").prepend("\n").prepend("----------");
                    $("#log").prepend("\n").prepend("Server original: " + response.unencrypted);
                    $("#log").prepend("\n").prepend("Server sent: " + response.encrypted);
                    var decryptedString = $.jCryption.decrypt(response.encrypted, password);
                    $("#log").prepend("\n").prepend("Decrypted: " + decryptedString);
                }
            });
        });
				
        $("#decrypt").click(function() {
            var decryptedString = $.jCryption.decrypt($("#text").val(), password);
            $("#log").prepend("\n").prepend("----------");
            $("#log").prepend("\n").prepend("Decrypted: " + decryptedString);
        });
    });
    
    
</script>