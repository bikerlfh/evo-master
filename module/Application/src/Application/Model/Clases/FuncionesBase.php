<?php

namespace Application\Model\Clases;

/**
 * Clase que contiene los métodos que se usan en los controller.
 *
 * @author VAIO
 */
class FuncionesBase {
    
    //Método que retorna el tipo de mensaje retornar al cliente.
    //el parámetro propio se envia true cuando se necesita mostrar un mensaje que no esta registrado en el serviceLocator
    public static function consultarMessage($serviceLocator,$nameMensaje,$propio = false) {
        $mensaje = null;
        if(!$propio)
        {
            $mensaje = $serviceLocator['MsgCrud'];
            $mensaje = $mensaje[$nameMensaje];
        } else
        {
            $mensaje = array('title'=> 'Error', 'message'=> htmlspecialchars($nameMensaje,ENT_QUOTES),'function'=> 'showMessageError');
        }
        return $mensaje['function'] . "('" . $mensaje['title'] . "','" . $mensaje['message'] . "');";
    }
    //Método que oculta los botones del crud.
    public static function configurarBotonesFormulario($form,$modificarBool)
    {
        if ($modificarBool) {
            $form->get("btnGuardar")->setAttribute("type", "hidden");
            $form->get("btnModificar")->setAttribute("type", "submit");
            $form->get("btnEliminar")->setAttribute("type", "button");
        } else {
            $form->get("btnGuardar")->setAttribute("type", "submit");
            $form->get("btnModificar")->setAttribute("type", "hidden");
            $form->get("btnEliminar")->setAttribute("type", "hidden");
        }

    }
    
}
