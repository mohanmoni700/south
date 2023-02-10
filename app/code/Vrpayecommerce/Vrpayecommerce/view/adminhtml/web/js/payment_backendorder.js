function changeDefaultRegistrationId(group)
{
	document.getElementById(group + '_default_reg_id').checked = true;
}

function changePayment(method, group)
{
	var groups = ['cc', 'dd', 'paypal'];
	for (i=0; i<groups.length; i++) {
		if (document.getElementById(groups[i] + '_select_payment')) {
			if (groups[i] == group) {
				document.getElementById(groups[i] + '_select_payment').style.display = 'block';
			} else {
				document.getElementById(groups[i] + '_select_payment').style.display = 'none';
			}
		}
	}

	document.getElementById('p_method_' + method).checked = true;
	document.getElementById(group + '_default_reg_id').checked = true;
}

function selectPayment(id, group)
{
	document.getElementById(group + '_default_reg_id').value = document.getElementById('reg_id_' + id).value;
	document.getElementById(group + '_default_reg_id').checked = true;
	document.getElementById(group + '_default_payment').innerHTML = document.getElementById('default_payment_' + id).innerHTML;
	document.getElementById(group + '_default_img').src = document.getElementById('default_img_' + id).getAttribute('src');
	document.getElementById(group + '_select_payment').style.display = 'none';
}
