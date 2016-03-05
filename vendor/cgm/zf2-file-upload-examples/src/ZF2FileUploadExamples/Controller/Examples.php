<?php

namespace ZF2FileUploadExamples\Controller;

use ZF2FileUploadExamples\Form;
use ZF2FileUploadExamples\InputFilter;
use Zend\Debug\Debug;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Application\Model\Entity;

class Examples extends AbstractActionController
{
    /**
     * @var Container
     */
    protected $sessionContainer;
    /**
     * @var Imagen
     */
    private $Imagen;
    private $Equipo;
    private $Accesorio;
    private $Planpostpago;
    private $Planlinea;

    public function __construct()
    {
        $this->sessionContainer = new Container('file_upload_examples');
    }

    public function indexAction()
    {
        // NOTE: Instead of using the following code, I'd suggest having a
        // background process/cron clear out old/stale temporary file uploads,
        // such as using "tmpwatch" or "tmpreaper" linux commands.
        // Do not use this in a real site. It's a quick & dirty cleanup method for
        // the purposes of the example.
        //array_map('unlink', glob('./data/tmpuploads/*'));

        return array();
    }

    public function successAction()
    {
        return array(
            'formData' => $this->sessionContainer->formData,
        );
    }

    protected function redirectToSuccessPage($formData = null)
    {
        $this->sessionContainer->formData = $formData;
        $response = $this->redirect()->toRoute('fileupload/success');
        $response->setStatusCode(303);
        return $response;
    }

    /**
     * Example of a single file upload form.
     *
     * @return array|ViewModel
     */
    public function singleAction()
    {
        $form = new Form\SingleUpload('file-form');

        if ($this->getRequest()->isPost()) {
            // Postback
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $form->setData($data);
            if ($form->isValid()) {
                //
                // ...Save the form...
                //
                return $this->redirectToSuccessPage($form->getData());
            }
        }

        return array('form' => $form);
    }

    /**
     * Example of a single File element using the HTML5 "multiple" attribute.
     *
     * @return array|ViewModel
     */
    public function multiHtml5Action()
    {
        $form = new Form\MultiHtml5Upload('file-form');
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');
        if ($this->getRequest()->isPost()) 
        {
            // Postback
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $form->setData($data);
            if ($form->isValid()) 
            {
                
                $this->Imagen=new Entity\Imagen($this->dbAdapter);
                if ($this->Imagen->insertImagen($form->getData())) {
                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrator/crud?msg=okSave');  
                }
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrator/crud?msg=errorSave');  
                 //return $this->redirect()->toUrl($this->getRequest()->getBaseUrl().'/administrator/crud');   
                //
                // ...Save the form...
                //
                //return $this->redirectToSuccessPage($form->getData());
            }
        }
        
        $tipo=$this->params()->fromQuery('tipo');
        $id=$this->params()->fromQuery('id');
        $descripcionTipo="";
        $carpeta="";
        switch($tipo)
        {
            case '01': // Equipo
                
                $this->Equipo=new Entity\Equipo($this->dbAdapter);
                $this->Equipo->getEquipoPoridequipo($id);
                $descripcionTipo="Equipo: ".$this->Equipo->getnombre();
                $carpeta="equipo";
                break; 
            case '02': // Accesorio
                $this->Accesorio=new Entity\Accesorio($this->dbAdapter);
                $this->Accesorio->getAccesorioPoridaccesorio($id);
                $descripcionTipo="Accesorio: ".$this->Accesorio->getnombre();
                $carpeta="accesorio";
                break;
            case '03': // Plan Postpago
                $this->Planpostpago=new Entity\Planpostpago($this->dbAdapter);
                $this->Planpostpago->getPlanpostpagoPoridplanpostpago($id);
                $descripcionTipo="Plan Postpago : ".$this->Planpostpago->getnombre();
                $carpeta="postpago";
                break;
            case '04': // Plan Linea
                $this->Planlinea=new Entity\Planlinea($this->dbAdapter);
                $this->Planlinea->getPlanlineaPoridplanlinea($id);
                $descripcionTipo="Plan Linea: ".$this->Planlinea->getdescripcion();
                $carpeta="linea";
                break;
        }
        $form->get("id")->setValue($id);
        $form->get("tipo")->setValue($tipo);
        //<== se reasigna el layout de los administradores ==>
        $this->layout("layout/admin.phtml");
        $view = new ViewModel(array(
           'legend' => $descripcionTipo,
           'form'   => $form,
           'title'=>'Subir imagenes'
        ));
        $view->setTemplate('zf2-file-upload-examples/examples/single');
        return $view;
    }

    /**
     * Example of a single File element using the HTML5 "multiple" attribute.
     *
     * @return array|ViewModel
     */
    public function collectionAction()
    {
        $form = new Form\CollectionUpload('file-form');

        if ($this->getRequest()->isPost()) {
            // Postback
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $form->setData($data);
            if ($form->isValid()) {
                //
                // ...Save the form...
                //
                return $this->redirectToSuccessPage($form->getData());
            }
        }

        return array('form' => $form);
    }

    /**
     * Example of a single file upload when form is partially valid.
     *
     * @return array|ViewModel
     */
    public function partialAction()
    {
        $form        = new Form\SingleUpload('file-form');
        $inputFilter = $form->getInputFilter();
        $container   = new Container('partialExample');
        $tempFile    = $container->partialTempFile;

        if ($this->getRequest()->isPost()) {
            // POST Request: Process form
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            // Disable required file input if we already have an upload
            if (isset($tempFile)) {
                $inputFilter->get('file')->setRequired(false);
            }

            $form->setData($data);
            if ($form->isValid()) {
                // If we did not get a new file upload this time around, use the temp file
                $data = $form->getData();
                if (isset($data['file']['error']) && $data['file']['error'] !== UPLOAD_ERR_OK) {
                    $data['file'] = $tempFile;
                }

                //
                // ...Save the form...
                //

                return $this->redirectToSuccessPage($data);
            } else {
                // Extend the session
                $container->setExpirationHops(1, 'partialTempFile');

                // Form was not valid, but the file input might be...
                // Save file to a temporary file if valid.
                $data = $form->getData();
                $fileErrors = $form->get('file')->getMessages();
                if (empty($fileErrors) && isset($data['file']['error'])
                    && $data['file']['error'] === UPLOAD_ERR_OK
                ) {
                    // NOTE: $data['file'] contains the filtered file path.
                    // 'FileRenameUpload' Filter has been run, and moved the file.
                    $container->partialTempFile = $tempFile = $data['file'];
                }
            }
        } else {
            // GET Request: Clear previous temp file from session
            unset($container->partialTempFile);
            $tempFile = null;
        }

        $view = new ViewModel(array(
           'title'     => 'Partial Validation Examples',
           'form'      => $form,
           'tempFiles' => (isset($tempFile)) ? array($tempFile) : null,
        ));
        $view->setTemplate('zf2-file-upload-examples/examples/single');
        return $view;
    }
}
