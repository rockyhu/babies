<?php
namespace Admin\Controller;

class PluploadController extends AuthController {
    
    public function index() {
        $this->display('Product/plupload');
    }
    
}