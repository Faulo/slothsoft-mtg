<?php
namespace Slothsoft\MTG;

$url = 'http://www.wizards.com/Magic/PlaneswalkerPoints/Login/GetEncryptValues';

$options = [];
$options['method'] = 'POST';

$user = 'Faulo';
$password = 'Fault1er@wizards.lio';

if ($data = \Slothsoft\Core\Storage::loadExternalJSON($url, 0, null, $options)) {
    $encrypt1 = $data['ModalData']['ResponseData'][0];
    $encrypt2 = $data['ModalData']['ResponseData'][1];
    $encrypt3 = $data['ModalData']['ResponseData'][2];
    
    $key = new RSAKeyPair($encrypt1, '0', $encrypt2);
    $encrypted = $key->encryptUser($user, $password, $encrypt3);
    
    $param = $data;
    $param['Parameters'] = [];
    $param['Parameters']['encrypted'] = $encrypted;
    $param['Parameters']['rememberMe'] = false;
    
    $url = 'http://www.wizards.com/Magic/PlaneswalkerPoints/Login/Login';
    
    if ($data = \Slothsoft\Core\Storage::loadExternalJSON($url, 0, $param, $options)) {
        my_dump($data);
    } else {
        throw new \Exception(\Slothsoft\Core\Storage::loadExternalHeader($url, 0, $param, $options));
    }
} else {
    throw new \Exception(\Slothsoft\Core\Storage::loadExternalFile($url, 0, null, $options));
}

/*
jQuery.post(GetUrl('/Login/GetEncryptValues'), function (data) {
	if (HandleModalResult(data) == "custom") {
		var encrypt1 = data.ModalData.ResponseData[0];
		var encrypt2 = data.ModalData.ResponseData[1];
		var encrypt3 = data.ModalData.ResponseData[2];

		if (userName.length > 0 && password.length > 0) {
			setMaxDigits(131);

			var keyPair = new RSAKeyPair(encrypt1, '', encrypt2);
			var encryptedUserNameAndPassword = encryptedString(keyPair, encrypt3 + '\\' + Base64.encode(userName) + '\\' + Base64.encode(password));
			var modalData = data;

			modalData.Parameters = {
				encrypted: encryptedUserNameAndPassword,
				rememberMe: $('#RememberMe').is(':checked')
			};

			jQuery.post(GetUrl('/Login/Login'), SerializeModalData(modalData), function (loginModalData) {
				if (loginModalData.Result == "ok") {
					if (loginModalData.ModalData.ResponseData) {
						submit.attr('disabled', false);
						checkingPasswordIdle = true;
						CompleteProgressBar('DCINumberProgressBar', '212', maxLength, true, null, null);
						CompleteProgressBar('PasswordProgressBar', '212', maxLength, false, null, function () {
							HandleModalResult(loginModalData);
						});
					} else {
						CompleteProgressBar('DCINumberProgressBar', '212', maxLength, true, null, null);
						CompleteProgressBar('PasswordProgressBar', '212', maxLength, true, function () {
							$('.RememberMe').css('display', 'none');
							$('#SignInModalError').fadeIn();
						}, function () {
							submit.attr('disabled', false);
							checkingPasswordIdle = true;
						});
					}
				}
			});
		}
	}
});
//*/