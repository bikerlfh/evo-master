/*
 * script ajax y validaciones 
 * Hecho por Luis Fernando Henriquez Arcinegas
 * */

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
        // Se obtiene el id del modal que se va a crear
        var idModal = modal.attr('id').toString().split("textModal")[1].length > 0? parseInt(modal.attr('id').toString().split("textModal")[1]) + 1 : 1;
        $(button).attr('data-target','#textModal'+idModal);
        // Div del modal a crear
        var divModal = "<div class='modal fade' id='textModal"+idModal+"' tabindex='0' role='dialog' aria-labelledby='textModalLabel"+idModal+"' aria-hidden='true' data-backdrop='static' data-keyboard='false' style='display: none;'>";
        divModal += "<div class='modal-dialog' id='modal-dialog-display"+idModal+"'></div></div>";
        // Se agrega al final del body el modal.
        $("body").append(divModal);
        // Se agrega el evento de borrar el modal
        $('#textModal'+idModal).on('hidden.bs.modal', function () {
            $('#textModal'+idModal).last().remove();
        });
        usar_ajax(url,'#modal-dialog-display'+idModal,parametrosPost);
    }
}

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