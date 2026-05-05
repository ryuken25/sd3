<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session()->get('role');
        
        // Ensure the arguments are parsed array of allowed roles
        if ($arguments && !in_array($role, $arguments)) {
            // Redirect to their respective dashboard instead of showing forbidden directly
            if ($role === 'admin') return redirect()->to(base_url('admin/dashboard'));
            if ($role === 'guru') return redirect()->to(base_url('guru/dashboard'));
            if ($role === 'orang_tua') return redirect()->to(base_url('orangtua/dashboard'));
            
            return redirect()->to(base_url('/')); 
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
