<?php

namespace ZF2FileUploadExamples\Form;

use Zend\InputFilter;
use Zend\Form\Form;
use Zend\Form\Element;

class MultiHtml5Upload extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->addElements();
        $this->setInputFilter($this->createInputFilter());
    }

    public function addElements()
    {
        // File Input
        $file = new Element\File('file');
        $file
            ->setLabel('Imagenes')
            ->setAttributes(array('multiple' => true));
        $this->add($file);

        // Text Input
        $text = new Element\Text('text');
        $text->setLabel('Descripcion');
        $this->add($text);
        
        // id de la tabla donde se va a guardar
        $id = new Element\Hidden('id');
        $this->add($id);
        
        // tipo imagen (01 => Equipo, 02 => accesorio)
        $tipo = new Element\Hidden('tipo');
        $this->add($tipo);
    }

    public function createInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();

        // File Input
        $file = new InputFilter\FileInput('file');
        $file->setRequired(true);
        $file->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'          => './public/images/',
                'overwrite'       => true,
                'use_upload_name' => true,
            )
        );
        //$file->getValidatorChain()->addByName(
        //    'fileextension', array('extension' => 'txt')
        //);
        $inputFilter->add($file);
        // Text Input
        $text = new InputFilter\Input('text');
        $text->setRequired(true);
        $inputFilter->add($text);

        return $inputFilter;
    }
}