/*
 * script ajax y validaciones 
 * Hecho por Luis Fernando Henriquez Arcinegas
 * */
function limpiarformulario(formulario){
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
      }
   });
 
   /* Se encarga de leer todas las etiquetas select del formulario */
   $(formulario).find('select').each(function() {
       $("#"+this.id + " option[value=0]").attr("selected",true);
   });
   /* Se encarga de leer todas las etiquetas textarea del formulario */
   $(formulario).find('textarea').each(function(){
      $(this).val('');
   });
}
//<=== funcion que se utiliza en la busqueda de objetos para ====>
function seleccionarObjetoBusqueda(campoid,campotext,Value,Text)
{
    //alert('a');
    if (confirm('Desea seleccionar este? ')) {
        $("#"+campoid).val(Value);
        $("#"+campotext).val(Text.replace(/_/g,' '));
        $("#btnCerrar").click();
    }
}
//<=========================================================>
//------------ REGION ADMINISTRADOR VISTA PROMOCION -------->
//<=========================================================>
//<==== valida que en las promociones solo se tomo un objeto ejm, si selecciona accesorios debe borrar todos los demas ===>
function limpiarObjetosVistaPromocion()
{
    $('#equipo').val("");
    $('#idequipo').val("");
    $('#planpostpago').val("");
    $('#idplanpostpago').val("");
    $('#planlinea').val("");
    $('#idplanlinea').val("");
    $('#accesorio').val("");
    $('#idaccesorio').val("");
}
//<=========================================================>
//------------ REGION ADMINISTRADOR VISTA CLIENTE -------->
//<=========================================================>
//<==== valida que en las promociones solo se tomo un objeto ejm, si selecciona accesorios debe borrar todos los demas ===>
function limpiarObjetosVistaCliente()
{
    $('#equipo').val("");
    $('#idequipo').val("");
    $('#planpostpago').val("");
    $('#idplanpostpago').val("");
    $('#planlinea').val("");
    $('#idplanlinea').val("");
    $('#accesorio').val("");
    $('#idaccesorio').val("");
    $('#promocion').val("");
    $('#idpromocion').val("");
}
//<=========================================================>
function usar_ajax(URL,objeto,datos)
{	
    $.ajax(
    {
        beforeSend: function(){
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
            //se ejecuta despues de succes o error
            console.log(estado);
        },
        timeout:10000 //tiempo maximo de espera
    });
}
function validarTecla(elEvento,permitidos)
{
    var numeros="0123456789";
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
    toastr.info(menssage, title);
}
function showMessageError(title,menssage)
{
    toastr.error(menssage, title);
}
function showMessageSuccess(title,menssage)
{
    toastr.success(menssage, title);
}

function clearForm(formulario){
   /* Se encarga de leer todas las etiquetas input del formulario*/
   $("#"+formulario).find('input').each(function() {
      switch(this.type) {
         case 'password':
         case 'text':
         case 'hidden':
              $(this).val('');
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
         case 'checkbox':
         case 'radio':
              this.checked = false;
      }
   });
   $('#'+formulario).find('select').each(function() {    	
        $(this).prop('selectedIndex',0);
    });
}

function ajax_2form(form,datos2)//funcion hecha para vincular los usuarios
{
    var respuesta;
    var datos=$("#"+form).serialize()+'&'+datos2;
    $("#"+form).unbind('submit');
    $("#"+form).on('submit',function(e)
    {
        e.preventDefault();//previene que el formulario haga submit
        var URL=$("#"+form).attr("action");
        var metodo=$("#"+form).attr("method");
        $.ajax(
        {
            beforeSend: function(){
                    //codigo q se ejecutará antes de q se inicie ajax;				
               mostrarImgCargando();
            },
            type: metodo,
            url: URL,
            data: datos,
            success: function(resp){
                    //recibe la la respuesta de ajax 
                    $('#divhidden').html(resp);//mostramos la respuesta en el objeto	
                    respuesta=resp;
                    console.log(resp)//se muestra tambien en la consola			
            },
            error: function(jqXHR,estado,error){
                    console.log(error)
                    console.log(estado)
            },
            complete: function(jqXHR,estado)
            {			
                    //se ejecuta despues de succes o error
                    esconderImgCargando();
            },
            timeout:10000 //tiempo maximo de espera
        })
    });
}

function ajax_form(form)
{
    var respuesta;
    var datos=$("#"+form).serialize();
    $("#"+form).unbind('submit');
    $("#"+form).on('submit',function(e)
    {
        e.preventDefault();//previene que el formulario haga submit
        var URL=$("#"+form).attr("action");
        var metodo=$("#"+form).attr("method");
        $.ajax(
        {
            beforeSend: function(){
                    //codigo q se ejecutará antes de q se inicie ajax;				
               mostrarImgCargando();
            },
            type: metodo,
            url: URL,
            data: datos,
            success: function(resp){
                //recibe la la respuesta de ajax 
                //$("#"+objeto).html(resp);//mostramos la respuesta en el objeto		
                respuesta=resp;
                console.log(resp)//se muestra tambien en la consola			
            },
            error: function(jqXHR,estado,error){
                console.log(error)
                console.log(estado)
            },
            complete: function(jqXHR,estado)
            {			
                //se ejecuta despues de succes o error
                esconderImgCargando();
                if(respuesta==true)
                {
                    crearAlerta('Operacion exitosa','exito','');
                    clearForm(form);
                }
                else
                    crearAlerta(respuesta,'alerta','');
            },
            timeout:10000 //tiempo maximo de espera
        })
    });
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