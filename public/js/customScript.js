/*
 * script ajax y validaciones 
 * Hecho por Luis Fernando Henriquez Arcinegas
 * */

/* Funcion para buscar los productos en la tienda de forma paginada
 * 
 * @param {int} pageSize   => Tamaño de items que se van a visualizar por pagina
 * @param {int} pageNumber => Número de la pagina actual
 * @param {string} basePath 
 * @returns {undefined}
 */
function buscarProductosEnTienda(pageSize,pageNumber,basePath)
{
    if (pageNumber == null) {
        pageNumber = $.get("pageNumber");    
    }
    var idMarca = $.get("idMarca");
    var idCategoria = $.get("idCategoria");
    var url = basePath+"/buscar?pageSize="+pageSize+"&pageNumber="+pageNumber+"&filtro="+$('#filtro').val();
    if (idMarca != null) {
        url += "&idMarca=" + idMarca;
    }
    if (idCategoria != null) {
        url += "&idCategoria=" + idCategoria;
    }
    $(location).attr('href',basePath+"/buscar?pageSize="+pageSize+"&pageNumber="+pageNumber+"&filtro="+$('#filtro').val());
}
/* Agrega al carro un producto
 * 
 * @param {long} idSaldoInventario
 * @param {decimal} cantidad
 * @param {string} basePath
 * @returns null
 */
function agregarProductoCart(idSaldoInventario, cantidad, basePath)
{
   usar_ajax(basePath + '/application/cart/addToCart','#zoneCart',"idSaldoInventario="+idSaldoInventario+"&qty="+cantidad);
}
/* Funcion para mostrar una busqueda en un modal dinamico.
 * Este moda se crea al momento de invocar la fucion y se destuye al momento de cerrarlo.
 * @param {type} button: boton donde es llamada la busqueda
 * @param {type} url   : Url de la vista de la busqueda
 * @param {type} parametrosPost : data post
 * @returns {null}
 */
function showBusquedaOnModal(button,url,parametrosPost)
{
    // Se verifica el ultimo modal que esta cargado
    if ($(".modal").last() != null) 
    {
        var modal = $(".modal").last();
        var idModal = 1;
        if (modal.attr('id') != undefined) {
            // Se obtiene el id del modal que se va a crear
            idModal = modal.attr('id').toString().split("textModal")[1].length > 0? parseInt(modal.attr('id').toString().split("textModal")[1]) + 1 : 1;
        }
        $(button).attr('data-target','#textModal'+idModal);
        // Div del modal a crear
        var style = jQuery.browser.mobile == true? "style='width:96%'":"";
        var divModal = "<div class='modal fade' id='textModal"+idModal+"' tabindex='0' role='dialog' aria-labelledby='textModalLabel"+idModal+"' aria-hidden='true' data-backdrop='true' data-keyboard='false' style='display: none;'>";
        divModal += "<div class='modal-dialog' "+style+" id='modal-dialog-display"+idModal+"'></div></div>";
        // Se agrega al final del body el modal.
        $("body").append(divModal);
        if (url.indexOf("?") >= 0){
            url+='&';
        }
        else{
            url+='?';
        }
        url+='modalDialogDisplay=modal-dialog-display'+idModal;
        usar_ajax(url,'#modal-dialog-display'+idModal,parametrosPost);
        
        // Se agrega el evento de borrar el modal
        $('#textModal'+idModal).on('hidden.bs.modal', function () {
            $('#textModal'+idModal).last().remove();
        });
    }
}
/**
 * Retorna el valor de un parametro de una cadena con formato URL
 * Se usa para no tener que enviar desde el controlador el nombre del modalDialogDisplay donde se debe mostrar la busqueda*/
(function($) {  
    $.getParameter = function(key,url)   {  
        key = key.replace(/[\[]/, '\\[');  
        key = key.replace(/[\]]/, '\\]');  
        var pattern = "[\\?&]" + key + "=([^&#]*)";  
        var regex = new RegExp(pattern);  
        var url = unescape(url);  
        var results = regex.exec(url);  
        if (results === null) {  
            return null;  
        } else {  
            return results[1];  
        }  
    }  
})(jQuery);
// Se activa del menu el formulario que este visible
// y muestra mensajes del crud que se pasan por get
$(document).ready(function()
{   
    //showBusqueda("/evo-master/admin/datobasicotercero/buscar","");
    //********** activa el menu del formulario q esta visible************/
    $(document).find('a').each(function() 
    {
        if($(location).attr("href").indexOf($(this).attr("href"))>-1)
        {
            $(this).parents("li").each(function()
            {
                $(this).children("ul").attr("style","display: block;");
                $(this).attr("class","expanded");
            });
            $(this).attr("class","active");
            return;
        }
    })
    //******************************************************************/
    // visualizamos el mensaje del crud que se envió por get
    if ($.get("msg") != null) 
    {
        switch($.get("msg"))
        {
            case "okSave":
                showMessageSuccess("Operación Exitosa",'Se ha guardado con Exito');
                break;
            case "okUpdate":
                showMessageSuccess("Operación Exitosa",'Se ha modificado con Exito');
                break;
            case "okDelete":
                showMessageSuccess("Operación Exitosa",'Se ha eliminado con Exito');
                break;
            case "errorSave":
                showMessageError('Operación sin resultado','No se logro guardar');
                break;
            case "errorUpdate":
                showMessageError('Operación sin resultado','No se logro modificar');
                break;
            case "errorDelete":
                showMessageError('Operación sin resultado','No se logro eliminar');
                break;
            case "errorDesconocido":
                showMessageWarning('Erro desconocido','Ha ocurrido un error desconocido');
                break;
        }
    }
});

function limpiarformulario(formulario){
    $("#btnGuardar").attr("type","submit");
    $("#btnModificar").attr("type","hidden");
    $("#btnEliminar").attr("type","hidden");
    // estos dos campos son para el formulario de imagen
    $('#image-file').attr('type','file');
    $('#image-file').attr('multiple','multiple');
    $('#url').attr('type','hidden');
    $('#imgAdminProducto').attr('src','');
    
   /* Se encarga de leer todas las etiquetas input del formulario*/
   $(formulario).find('input').each(function() {
      switch(this.type) {
        case 'password':
        case 'text':
        case 'hidden':
            $(this).val('');
            break;
        case 'checkbox':
        case 'radio':
            this.checked = false;
            break;
        case 'number':
            $(this).val('0');
            break;
        case 'date':
            $(this).val('dd/mm/aaaa');
        break; 
        case 'time':
            $(this).val('--:--');
        break;
      }
   });
   /* Se encarga de leer todas las etiquetas select del formulario */
    $(formulario).find('select').each(function() {    	
        $(this).prop('selectedIndex',0);
    });
   /* Se encarga de leer todas las etiquetas textarea del formulario */
   $(formulario).find('textarea').each(function(){
      $(this).val('');
   });
}
//<=========================================================>
function usar_ajax(URL,objeto,datos)
{	
    $.ajax(
    {
        beforeSend: function(){
            $("#loaderCenter").show();
           //codigo q se ejecutará antes de q se inicie ajax;
           //mostrarImgCargando();
        },
        async: true,
        type: "POST",
        url: URL,
        data: datos,
        success: function(resp){
            //recibe la la respuesta de ajax 
            $(objeto).html(resp);//mostramos la respuesta en el objeto	
            //console.log(resp);
        },
        error: function(jqXHR,estado,error){
            console.log(error);
            console.log(estado);
        },
        complete: function(jqXHR,estado)
        {
            $("#loaderCenter").hide();
            //se ejecuta despues de succes o error
            console.log(estado);
        },
        timeout:10000 //tiempo maximo de espera
    });
}
function validarTecla(elEvento,permitidos)
{
    var numeros="0123456789.";
    var caracteres="abcdefghijklmnñopqrstuvwxyzABCDEFGHIJKLMNÑOPQRSTUVWXYZ_+-. ";
    var numeros_caracteres=numeros+caracteres;
    switch(permitidos)
    {
        case 'num':
                permitidos=numeros;
        break;

        case 'car':
                permitidos=caracteres;
        break;

        case 'num_car':
                permitidos=numeros_caracteres;
        break;
    }

    var evento=elEvento || window.event;
    var codigoCaracter=evento.charCode || evento.keyCode;
    var caracter=String.fromCharCode(codigoCaracter);	
    return permitidos.indexOf(caracter) != -1;			
}
function showMessaginfo(title,menssage)
{
    toastr.info(menssage, title); 
}
function showMessageWarning(title,menssage)
{
    toastr.warning(menssage, title);
}
function showMessageError(title,menssage)
{
    toastr.error(menssage, title);
}
function showMessageSuccess(title,menssage)
{
    toastr.success(menssage, title);
}

function centrarObjeto (campo){
	
    var correctorPercent = 1;//En caso de definir las dimensiones del wrapper en porcentajes.
    if (window.innerWidth >  parseInt($(campo).css('width')) - correctorPercent){
        $(campo).css('width', parseInt($(campo).css('width')));
        $(campo).css('left','50%');
        $(campo).css('margin-left',((-1) * parseInt($(campo).css('width')) / 2) +'px');
    }
    if (window.innerHeight >  parseInt($(campo).css('height'))- correctorPercent){
        $(campo).css('top','50%');
        $(campo).css('margin-top', ((-1) *  parseInt($(campo).offset('Height')) / 2) +'px');
    }
}

//Esta función se agrega cuando se inicia un popup, con el fin de que borre el contenido que tiene actualmente.
//Parametros true cuando se maneje dialog de primera instancia, y false cuando esta dentro de otro dialog.
function LimpiarModal(button, contenidoModal, modal){
    
    $("#" + button).click(function(){
        $("#" + contenidoModal).html("");
        $('#' + modal).modal('hide');
    });
}

//<== funcion que captura un parametro GET ===>
(function($) {  
    $.get = function(key)   {  
        key = key.replace(/[\[]/, '\\[');  
        key = key.replace(/[\]]/, '\\]');  
        var pattern = "[\\?&]" + key + "=([^&#]*)";  
        var regex = new RegExp(pattern);  
        var url = unescape(window.location.href);  
        var results = regex.exec(url);  
        if (results === null) {  
            return null;  
        } else {  
            return results[1];  
        }  
    }  
})(jQuery);
// Determina si el cliente es un movil o un pc
// jQuery.browser.mobile : boolean
(function(a) {
(jQuery.browser = jQuery.browser || {}).mobile = /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))
})(navigator.userAgent || navigator.vendor || window.opera);