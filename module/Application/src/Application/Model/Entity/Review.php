<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;

class Review extends AbstractTableGateway
{
    private $idReview;
    private $idPedidoVentaPosicion;
    private $puntuacion;
    private $mensaje;
    private $idUsuarioCreacion;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('Review', 'Venta');
    }
    
    function getIdReview() {
        return $this->idReview;
    }

    function getIdPedidoVentaPosicion() {
        return $this->idPedidoVentaPosicion;
    }

    function getMensaje() {
        return $this->mensaje;
    }

    function getIdUsuarioCreacion() {
        return $this->idUsuarioCreacion;
    }

    function setIdReview($idReview) {
        $this->idReview = $idReview;
    }

    function setIdPedidoVentaPosicion($idPedidoVentaPosicion) {
        $this->idPedidoVentaPosicion = $idPedidoVentaPosicion;
    }

    function setMensaje($mensaje) {
        $this->mensaje = $mensaje;
    }

    function setIdUsuarioCreacion($idUsuarioCreacion) {
        $this->idUsuarioCreacion = $idUsuarioCreacion;
    }

    public function guardarReview($idPedidoVentaPosicion,$puntuacion,$mensaje,$idUsuarioCreacion)
    {
        $datos=array(
                'idPedidoVentaPosicion'=> $idPedidoVentaPosicion,
                'puntuacion'=> $puntuacion,
                'mensaje'=> $mensaje,
                'idUsuarioCreacion'=> $idUsuarioCreacion);
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarReview($idReview,$idPedidoVentaPosicion,$puntuacion,$mensaje)
    {
        $datos=array(
                'idPedidoVentaPosicion'=> $idPedidoVentaPosicion,
                'puntuacion'=> $puntuacion,
                'mensaje'=> $mensaje);
      
        $result=$this->update($datos,array('idReview'=>$idReview));
        if ($result > 0) {
            return true;
        }
        return false;
    }
    public function eliminarReview($idReview)
    {
        if ($this->delete(array('idReview'=>$idReview)) > 0)
            return true;
        return false;
    }
    
    public function consultarReviewPorIdReview($idReview)
    {
        $result=$this->select(array('idReview'=>$idReview))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    
    public function consultarReviewPorIdPedidoVentaPosicion($idPedidoVentaPosicion)
    {
        $result=$this->select(array('idPedidoVentaPosicion'=>$idPedidoVentaPosicion))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    
    private function LlenarEntidad($result)
    {
        $this->idReview=$result['idReview'];
        $this->idPedidoVentaPosicion=$result['idPedidoVentaPosicion'];
        $this->puntuacion=$result['puntuacion'];
        $this->mensaje=$result['mensaje'];
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
    }
}

