<?php
namespace Application\Model\Entity;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Expression;
use Application\Model\Clases;
use Application\Model\Entity;

class SaldoInventario extends AbstractTableGateway
{
    private $idSaldoInventario;
    private $idProducto;
    private $idProveedor;
    private $cantidad;
    private $costoTotal;
    private $valorVenta;
    private $url;
    private $estado;
    private $idUsuarioCreacion;
    private $idUsuarioModificacion;
    private $fechaCreacion;
    private $fechaModificacion;
    
    
    public $Producto;
    public $Proveedor;
    
    public function __construct(Adapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->table =  new \Zend\Db\Sql\TableIdentifier('SaldoInventario', 'Inventario');
    }

    public function getFechaModificacion(){
        return $this->fechaModificacion;
    }
    public function setFechaModificacion($fechaModificacion){
        $this->fechaModificacion=$fechaModificacion;
    }
    public function getFechaCreacion(){
        return $this->fechaCreacion;
    }
    public function setFechaCreacion($fechaCreacion){
        $this->fechaCreacion=$fechaCreacion;
    }
    public function getIdUsuarioModificacion(){
        return $this->idUsuarioModificacion;
    }
    public function setIdUsuarioModificacion($idUsuarioModificacion){
        $this->idUsuarioModificacion=$idUsuarioModificacion;
    }
    public function getIdUsuarioCreacion(){
        return $this->idUsuarioCreacion;
    }
    public function setIdUsuarioCreacion($idUsuarioCreacion){
        $this->idUsuarioCreacion=$idUsuarioCreacion;
    }
    function getEstado() {
        return $this->estado;
    }
    function setEstado($estado) {
        $this->estado = $estado;
    }
    function getUrl() {
        return $this->url;
    }
    function setUrl($url) {
        $this->url = $url;
    }
    public function getValorVenta(){
        return $this->valorVenta;
    }
    public function setValorVenta($valorVenta){
        $this->valorVenta=$valorVenta;
    }
    public function getCostoTotal(){
        return $this->costoTotal;
    }
    public function setCostoTotal($costoTotal){
        $this->costoTotal=$costoTotal;
    }
    public function getCantidad(){
        return $this->cantidad;
    }
    public function setCantidad($cantidad){
        $this->cantidad=$cantidad;
    }
    public function getIdProveedor(){
        return $this->idProveedor;
    }
    public function setIdProveedor($idProveedor){
        $this->idProveedor=$idProveedor;
    }
    public function getIdProducto(){
        return $this->idProducto;
    }
    public function setIdProducto($idProducto){
        $this->idProducto=$idProducto;
    }
    public function getIdSaldoInventario(){
        return $this->idSaldoInventario;
    }
    public function setIdSaldoInventario($idSaldoInventario){
        $this->idSaldoInventario=$idSaldoInventario;
    }
    
    public function guardarSaldoInventario($idProducto,$idProveedor,$cantidad,$costoTotal,$valorVenta,$url,$estado,$fecha,$idUsuarioCreacion)
    {
        $datos=array(
                'fechaModificacion'=> $fecha,
                'fechaCreacion'=> $fecha,
                'idUsuarioModificacion'=> $idUsuarioCreacion,
                'idUsuarioCreacion'=> $idUsuarioCreacion,
                'estado' => $estado,
                'url' => $url,
                'valorVenta'=> $valorVenta,
                'costoTotal'=> $costoTotal,
                'cantidad'=> $cantidad,
                'idProveedor'=> $idProveedor,
                'idProducto'=> $idProducto
        );
        $result=$this->insert($datos);
        if($result>0)
            return true;
        return false;
    }

    public function modificarSaldoInventario($idSaldoInventario,$idProducto,$idProveedor,$cantidad,$costoTotal,$valorVenta,$url,$estado,$fecha,$idUsuarioModificacion)
    {
        $datos=array(
                'fechaModificacion'=> $fecha,
                'idUsuarioModificacion'=> $idUsuarioModificacion,
                'estado'=>$estado,
                'url' => $url,
                'valorVenta'=> $valorVenta,
                'costoTotal'=> $costoTotal,
                'cantidad'=> $cantidad,
                'idProveedor'=> $idProveedor,
                'idProducto'=> $idProducto
        );
        $result=$this->update($datos,array('idSaldoInventario'=>$idSaldoInventario));
        if($result>0)
            return true;
        return false;
    }
    public function eliminarSaldoInventario($idSaldoInventario)
    {
        if ($this->delete(array('idSaldoInventario'=>$idSaldoInventario))>0) {
            return true;
        }
        return false;
    }
    public function consultarTodoSaldoInventario()
    {
        $sql = new Sql($this->adapter);        
        $select = $sql->select()->
                        from(array('s'=> $this->table))->
                        join(array("p"=> new TableIdentifier("Producto","Producto")),
                                    "s.idProducto = p.idProducto",
                                    array("nombreProducto"=> new Expression("p.codigo + ' - ' + p.nombre")))->
                        join(array("proveedor"=> new TableIdentifier("Proveedor","Tercero")),
                                    "s.idProveedor = proveedor.idProveedor")->
                        join(array("dbt"=> new TableIdentifier("DatoBasicoTercero","tercero")),
                                   "proveedor.idDatoBasicoTercero = dbt.idDatoBasicoTercero",
                                    array("descripcionProveedor"=> new Expression("convert(varchar,dbt.nit) + ' - ' + dbt.descripcion")));
        
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($results)->toArray();
    }
    public function consultarSaldoInventarioPorIdSaldoInventario($idSaldoInventario)
    {
        $result=$this->select(array('idSaldoInventario'=>$idSaldoInventario))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    public function consultarSaldoInventarioPorIdProducto($idProducto)
    {
        return $this->select(array('idProducto'=>$idProducto))->toArray();
    }
    public function consultarSaldoInventarioPorIdProveedor($idProveedor)
    {
        return $this->select(array('idProveedor'=>$idProveedor))->toArray();
    }
    public function consultarSaldoInventarioPorIdProductoIdProveedor($idProducto,$idProveedor)
    {
        $result = $this->select(array('idProducto'=>$idProducto,'idProveedor'=>$idProveedor))->current();
        if($result)
        {
            $this->LlenarEntidad($result);
            return true;
        }
        return false;
    }
    
    public function consultaAvanzadaSaldoInventario($idProducto, $idProveedor)
    {
        $where = array();
        if ($idProducto > 0 ) {
            array_push($where,'idProducto='.$idProducto);
        }
        if ($idProveedor > 0 ) {
            array_push($where,'idProveedor='.$idProveedor);
        }
        $sql = new Sql($this->adapter);
        $select = $sql->select(new TableIdentifier("vConsultaAvanzadaSaldoInventario", "Inventario"))
                               ->where($where);
        
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $resultsSet = new ResultSet();
        return $resultsSet->initialize($result)->toArray();
    }
    
    private function LlenarEntidad($result)
    {
        $this->fechaModificacion=$result['fechaModificacion'];
        $this->fechaCreacion=$result['fechaCreacion'];
        $this->idUsuarioModificacion=$result['idUsuarioModificacion'];
        $this->idUsuarioCreacion=$result['idUsuarioCreacion'];
        $this->estado=$result['estado'];
        $this->url=$result['url'];
        $this->valorVenta=$result['valorVenta'];
        $this->costoTotal=$result['costoTotal'];
        $this->cantidad=$result['cantidad'];
        $this->idProveedor=$result['idProveedor'];
        $this->idProducto=$result['idProducto'];
        $this->idSaldoInventario=$result['idSaldoInventario'];
        
        $this->CargarEmbebidos();
    }
    //<===============================================================>
    //<--- Carga los objetos completos relacionados a este objeto ====>
    //<===============================================================>
    private function CargarEmbebidos()
    {
        $this->Producto =new Entity\Producto(parent::getAdapter());
        $this->Producto->consultarProductoPorIdProducto($this->idProducto);
        
        $this->Proveedor =new Entity\Proveedor(parent::getAdapter());
        $this->Proveedor->consultarProveedorPoridProveedor($this->idProveedor);
       
    }
}