/* Copyright (C) 2012  Governo do Estado do Rio Grande do Sul
 *
 *   Author: Leo Andrade <leo-andrade@procergs.rs.gov.br>
 *           Sergio Berlotto <sergio.berlotto@gmail.com>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

jQuery(document).ready(function() {

	jQuery('#upload_image_button').click(function() {
		formfield = jQuery('#upload_image').attr('name');
		tb_show('', 'media-upload.php?TB_iframe=true');
		return false;
	});
 
	window.send_to_editor = function(html) {
		cont = parseInt(jQuery('#totalAnexos').val())+1;
		jQuery('#moredetails').val($('#moredetails').val()+"<p>"+html+"</p>");
		jQuery('#totalAnexos').val(cont);
		jQuery('#moredetailsshow').append(_fnMoreDetailsShowAppend(cont, html));
		tb_remove();
	};
	
	cont = parseInt(jQuery('#totalAnexos').val());
	for (i =1; i<=cont;i++){
		jQuery('#removeAnexo_'+i).live('click', _txtRemove);
		
		jQuery('#txtOrd_'+i).live('blur', _txtOrdena);
	}
});

_txtOrdena = function() {
	orderant = jQuery(this).attr('defaultvalue');
	orderatu = jQuery(this).attr('value');
	if (parseInt(orderant) != parseInt(orderatu)){
		moredetails = _fnArrOrdena(orderant, orderatu);
	} 
	valMoreDetails = "<p>"+moredetails.join("<p>");
	jQuery('#moredetails').val(valMoreDetails);
	val_moredetailsShow = _fnMoredeDetailsShow(cont, jQuery('#moredetails').val());
	jQuery('#moredetailsshow').html(val_moredetailsShow); 
	
	return false;
};

_txtRemove = function() {
	cont = jQuery('#totalAnexos').val();
	var moredetails = jQuery('#moredetails').val().split("<p>");
	moredetails.shift();
	moredetails.splice(jQuery(this).attr('valexc')-1, 1);
	valMoreDetails = "<p>"+moredetails.join("<p>");
	jQuery('#moredetails').val(valMoreDetails);
	jQuery('#totalAnexos').val(cont-1);
	val_moredetailsShow = _fnMoredeDetailsShow(cont-1, jQuery('#moredetails').val());
	jQuery('#moredetailsshow').html(val_moredetailsShow); 
	
	return false; 
}	

function _fnRemovedivMoreDetails(cont){
	jQuery('#removeAnexo_'+cont).live('click', _txtRemove);
}

function _fnOrdenadivMoreDetails(cont){
	jQuery('#txtOrd_'+cont).live('blur', _txtOrdena);
}

function _fnMoreDetailsShowAppend(cont, html){
	txtShow = "";
	txtShow += "<div id='"+cont+"'>";
	txtShow += "<input id='txtOrder_"+cont+"' name='txtOrder_"+cont+"' class='txtOrder' type='text' value='"+cont+"' defaultvalue='"+cont+"'> - ";
	txtShow += "<input type='button' name='removeAnexo_"+cont+"' id='removeAnexo_"+cont+"' valexc='"+cont+"' class='btnUploadExcluir' onclick='_fnRemovedivMoreDetails("+cont+")' value='Remove'> - ";
    txtShow += " "+html;  
    txtShow += "</div>";
    
    return txtShow;
}

function numOrdArr(a, b){ return (a[0]-b[0]); }

function _fnArrOrdena(orderant, orderatu){

	var moredetails = jQuery('#moredetails').val().split("<p>");
	moredetails.shift();
	
	var auxArr = new Array();
	
	for (i in moredetails){
		auxArr[i] = new Array(2);
		numord = i
		if (i == orderant-1) { numord = orderatu; }
		auxArr[i][0] = numord;
		auxArr[i][1] = moredetails[i];
	}
	 
	for (i in  moredetails = auxArr.sort(numOrdArr)){
		moredetails[i] = moredetails[i][1];
	}
	return moredetails;
}

function _fnMoredeDetailsShow(cont, moredetails){
	
	var moredetails = moredetails.split("<p>");
	moredetails.splice(0,1);
	txtShow = "";
	for (i =1; i<=cont;i++){
		txtShow += "<div id='"+i+"'>";
		txtShow += "<input id='txtOrder_"+i+"' name='txtOrder_"+i+"' class='txtOrder' type='text' value='"+i+"' defaultvalue='"+i+"' onblur='_fnOrdenadivMoreDetails("+cont+")' > -";
		txtShow += "<input type='button' name='removeAnexo_"+i+"' id='removeAnexo_"+i+"' valexc='"+i+"' class='btnUploadExcluir' value='Remove'> -";
	    txtShow += " "+moredetails[i-1];  
	    txtShow += "</div>";
	}
	
	jQuery('#removeAnexo_'+i).click(_txtRemove);
	jQuery('#txtOrd_'+i).blur(_txtOrdena);
	
	return txtShow;
}