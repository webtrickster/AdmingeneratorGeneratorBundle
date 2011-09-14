<?php 


namespace Admingenerator\GeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator as BaseBundleGenerator;

use Symfony\Component\HttpKernel\Util\Filesystem;
use Symfony\Component\DependencyInjection\Container;

/**
 * Generates an admin bundle.
 *
 * @author Cedric LOMBARDOT
 */
class BundleGenerator extends BaseBundleGenerator
{
    private $filesystem;
    private $skeletonDir;
    
    protected $actions = array('New', 'List', 'Edit', 'Delete');
    
    protected $forms = array('New', 'Filters', 'Edit');

    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem = $filesystem;
        $this->skeletonDir = $skeletonDir;
    }

    public function generate($namespace, $bundle, $dir, $format, $structure)
    {
        $dir .= '/'.strtr($namespace, '\\', '/');
        if (file_exists($dir)) {
            throw new \RuntimeException(sprintf('Unable to generate the bundle as the target directory "%s" is not empty.', realpath($dir)));
        }

        list( $namespace_prefix, $bundle_name) = explode('\\', $namespace, 2);
        $parameters = array(
            'namespace'        => $namespace,
            'bundle'           => $bundle,
            'generator'        => 'admingenerator.generator.doctrine', //@todo make it dynamic when propel come
            'namespace_prefix' => $namespace_prefix,
            'bundle_name'      => $bundle_name,
        );

        $this->renderFile($this->skeletonDir, 'Bundle.php', $dir.'/'.$bundle.'.php', $parameters);
        
        foreach ($this->actions as $action) {
            $parameters['action'] = $action;
            $this->renderFile($this->skeletonDir, 'DefaultController.php', $dir.'/Controller/'.$action.'Controller.php', $parameters);
            
            if ('Delete' !== $action) {
                $this->renderFile($this->skeletonDir, 'index.html.twig', $dir.'/Resources/views/'.$action.'/index.html.twig', $parameters);
            }
        }
        
        foreach ($this->forms as $form) {
            $parameters['form'] = $form;
            $this->renderFile($this->skeletonDir, 'DefaultType.php', $dir.'/Form/Type/'.$form.'Type.php', $parameters);
        }
        
        $this->renderFile($this->skeletonDir, 'generator.yml', $dir.'/Resources/config/generator.yml', $parameters);
    }
}