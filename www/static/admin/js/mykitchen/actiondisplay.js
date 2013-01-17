/**
 * watch topic list(static/admin/js/aschool/watchtopic.js)
 * watchtopic list 
 * 
 * @copyright  Copyright (c) 2010 Community Factory Inc. (http://communityfactory.com)
 * @create    2010/04/13    xial   
 */

/*
chanag page 
*/
var $j = jQuery.noConflict();

$j().ready(function() {
	$j('#txtStartTime').datepick({dateFormat: 'yy-mm-dd'});
	$j('#txtEndTime').datepick({dateFormat: 'yy-mm-dd'});
});

function changeShop()
{
	var ajaxUrl = UrlConfig.BaseUrl + '/ajaxmykitchen/ajaxnblist';
	var buyType = $j("#shop").val();

	try {
	    $j.ajax({
		    type: "POST",   
		    url: ajaxUrl,
		    dataType: "json",
		    data: "CF_buyType=" + buyType,
		    success: function(responseObject) {
	            if (responseObject) {
	            	var selectHtml = showSelect(responseObject);
	            	document.getElementById('nbshop').innerHTML = selectHtml;
	            	//$j("#nbshop").innerHTML = selectHtml;
				}
			}
		});
	}catch (e) {
		alert(e);
	}
}

/**
 * show Select value
 * @param  object array
 * @return string
 */
function showSelect(array)
{					
	$html = '<option value="" ></option>';	
    //for each row data
    for (var i = 0 ; i < array.length ; i++) {        
        $html += '<option value='+ array[i].id +'>'+ array[i].name +'</option>';
    } 
   
    return $html;
}

/**
 * show application table
 * @param  object array
 * @return string
 */
function showType(type, id)
{
	if (null == type || '' == type || null == id || '' == id) {
		return false;
	}
	$('showType').value = type;
	$('typeId').value = id;	
	$('frmList').submit();	
}