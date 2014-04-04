var batch = false;
var batch_total = '';
var batch_i = 0;
var batch_a = new Array();
var error = false;

$('#toggle').click(function () {
    var allCheckboxes = $(".ttoggle");
    var notChecked = allCheckboxes.not(':checked');
    allCheckboxes.removeAttr('checked');
    notChecked.attr('checked', 'checked');
    return false;
});


$('.additional').click(function () {
    var tr = $(this).parents("tr:first").next("tr");
    if (tr.is(":visible")) {
        tr.hide();
        $(this).html('<img src="/themes/cp_themes/default/images/field_collapse.png" alt="expand">');
    }
    else {
        tr.show();
        $(this).html('<img src="/themes/cp_themes/default/images/field_expand.png" alt="expand">');
    }
});
/*
//@todo click on bio
$("td.chbox").click(function () {
	
    _tr = $(this).parent();
    _tr.find("td:first").addClass("wew");
    _toggle = _tr.find('input:last');
    if (_toggle.is(':checked')) {
        _toggle.attr('checked', false);
    } else {
        _toggle.attr('checked', true);
    };

});
*/





$(".spamlist-ch").click(function () {
    startLoading();

    mbr_check(this);

    return false;
});

function mbr_check(btn) {

    _tr = $(btn).parents("tr:first");
    _bt = $(btn);
    _td = _bt.parents("td:first");

    _chtrusted = _tr.find('input:last');
    _bt.hide();

    _td.append('<span class="load2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>');

    username = _tr.find(".username").html();
    email = _tr.find(".email").html();
    ip_address = (_tr.find(".ip_address").html()).replace(".", "_");

    url = url_base + "&D=cp&C=addons_modules&M=show_module_cp&module=mx_stop_spammers&method=checker&ip_address=" + ip_address + "&username=" + username + "&email=" + email;
    error = false;
    $.ajax({
        type: "GET",
        url: url,
        success: function (data) {
            if (data.username != undefined) {
                if ((data.username.appears == 1 && data.ip.appears == 1) || data.email.appears == 1) {

                    //_chbanned.attr('checked', true);
                    _chtrusted.attr('checked', true);
                    _tr.addClass("toban");
                    str = its_spam;

                    //	_bt.show();
                }
                else {
                    _chtrusted.attr('checked', false);
                    str = no_results;
                }
            }
            else {
                str = no_results;

            }
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            error = true;
        },
        complete: function () {
            _td.children(".load2").remove();
			 _td.children(".message").remove();
			
            if (error) {
                _bt.show();
                error = false;
            }
            else {
                _td.append('<span class="message">' + str + '</span>');
            }


            if ((batch == false) || (batch_i > batch_total)) {
                batch = false;
                stopLoading();
            } else {
                _tr = $(batch_a[batch_i]).parents("tr:first");
                _toggle = _tr.find('input:first');
                $("#loading").html(please_wait + (batch_i + 1) + " / " + (batch_total + 1));
                batch_i = batch_i + 1;
                mbr_check(_toggle);
            }


        }
    });
    return false;
}

$("#mbr_action").click(function () {
    val = $("#members_action").val();
		if (val != "untrusted" || val != "trusted" || val != "ban") {
		batch = false;
		batch_total = -1;
		batch_i = 0;

		$(".ttoggle:checked").each(function (i) {
			batch_a[i] = this;
			batch_total = i;
		});

		if (batch_total != -1) {
			$("#loading").html(please_wait + (batch_i + 1) + " / " + (batch_total + 1));
			startLoading();
			batch = true;
			_tr = $(batch_a[batch_i]).parents("tr:first");
			_toggle = _tr.find('input:first');
			batch_i = batch_i + 1;

			if (val == "ch_batch") {
				mbr_check(_toggle);
				return false;
			}

			if (val == 'banandsend') {
				mbr_block(_toggle, 'yes');
				return false;
			}

		}
	}
});

$(".block-ch").click(function () {
    startLoading();

    mbr_block(this, 'yes');

    return false;

});

function add2trusted(btn, reverse) {
    if (!reverse) { 
        var reverse = 'no';
    }
    _tr = $(btn).parents("tr:first");
    _bt = $(btn);
    _td = _bt.parents("td:first");
    _chtrusted = _tr.find('input:last');
    _bt.hide();

    _td.append('<span class="load2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>');


    member_id = _tr.find(".member_id").html();

    url = url_base + "&D=cp&C=addons_modules&M=show_module_cp&module=mx_stop_spammers&method=add2trusted&member_id=" + member_id + "&reverse=" + reverse;

    $.ajax({
        type: "GET",
        url: url,
        success: function (data) {
            if (data == "OK") {
				
            }
            else {
                _bt.show();
            }

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            error = true;
        },
        complete: function () {
            _td.children(".load2").remove();
			_td.children(".message").remove();
            if (error) {
                _bt.show();
                error = false;
            }

            if ((batch == false) || (batch_i > batch_total)) {
                batch = false;
                stopLoading();
            } else {
                _tr = $(batch_a[batch_i]).parents("tr:first");
                _toggle = _tr.find('input:first');
                $("#loading").html(please_wait + (batch_i + 1) + " / " + (batch_total + 1));
                batch_i = batch_i + 1;
                add2trusted(_toggle, reverse);
            }


        }
    });

    return false;

};


function mbr_block(btn, sfs) {
    if (!sfs) {
        var sfs = 'no';
    }

    _tr = $(btn).parents("tr:first");
    _bt = $(btn);
    _td = _bt.parents("td:first");
    _chtrusted = _tr.find('input:last');
    _bt.hide();

    _td.append('<span class="load2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>');

    //@todo - toban_prototype
    username = _tr.find(".username").html();
    email = _tr.find(".email").html();
    ip_address = (_tr.find(".ip_address").html()).replace(".", "_");
    member_id = _tr.find(".member_id").html();

    url = url_base + "&D=cp&C=addons_modules&M=show_module_cp&module=mx_stop_spammers&method=toban&ip_address=" + ip_address + "&username=" + username + "&email=" + email + "&member_id=" + member_id + "&sfs=" + sfs;

    $.ajax({
        type: "GET",
        url: url,
        success: function (data) {
            if (data == "OK") {
               
            }
            else {
                _bt.show();
            }

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            error = true;
        },
        complete: function () {
            _td.children(".load2").remove();
			_td.children(".message").remove();

            if (error) {
                _bt.show();
                error = false;
            }

            if ((batch == false) || (batch_i > batch_total)) {
                batch = false;
                stopLoading();
            } else {
                _tr = $(batch_a[batch_i]).parents("tr:first");
                _toggle = _tr.find('input:first');
                $("#loading").html(please_wait + (batch_i + 1) + " / " + (batch_total + 1));
                batch_i = batch_i + 1;
                mbr_block(_toggle, 'yes');
            }


        }
    });

    return false;

};

$("#expand_bio").click(function () {
    $(".additional").each(function (i) {
        $(this).parents("tr:first").next("tr").toggle();

    });
});

$(".additional").each(function (i) {
    $(this).parents("tr:first").next("tr").hide();

});



	function startLoading() {
		loading = true;

		$('#loading').css({
			'top'		: $(window).scrollTop(),
			'left'		: $(window).scrollLeft(),
		});

		$(window).bind('scroll', function() {
			$('#loading').css({'top' : $(window).scrollTop(), 'left' : $(window).scrollLeft()});
		}).bind('resize', function() {
			$('#loading').css({'width' : $(window).width(), 'height' : $(window).height()});
		});

		$('#loading').fadeIn('fast');
	};


    	function stopLoading() {
		loading = false;

		$(window).unbind('scroll').unbind('resize');
		$('#loading').fadeOut('fast');
	};

$('.details').hide();

var dialog = $('<div id="statusbox"></div>').dialog({
    autoOpen: false,
    width: 300,
    height: 300,
    modal: true,
    title: 'Done',
    buttons: {
        "Close": function () {
            $(this).dialog('close');

        },
		"Recheck": function() {
				$(this).dialog('close');
		}
    }
});

$(".recheck").live('click', function() {
            $(this).dialog('close');
});