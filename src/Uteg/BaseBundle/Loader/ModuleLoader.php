<?php
/**
 * Created by PhpStorm.
 * User: Mathias Scherer
 * Date: 09.09.2015
 * Time: 00:22
 */

namespace Uteg\BaseBundle\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ModuleLoader extends Loader
{
    public function load($bundName, $type = null)
    {

        $collection = new RouteCollection();

        if ($bundName != 'init') {
            $resource = "@$bundName/Controller";
            $type = 'annotation';

            $importedRoutes = $this->import($resource, $type);

            $collection->addCollection($importedRoutes);
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return 'module' === $type;
    }
}