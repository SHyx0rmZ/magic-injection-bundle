magic-injection-bundle
======================
[![Latest Stable Version](https://poser.pugx.org/ppokatilo/magic-injection-bundle/v/stable.svg)](https://packagist.org/packages/ppokatilo/magic-injection-bundle)
[![Total Downloads](https://poser.pugx.org/ppokatilo/magic-injection-bundle/downloads.svg)](https://packagist.org/packages/ppokatilo/magic-injection-bundle)
[![Latest Unstable Version](https://poser.pugx.org/ppokatilo/magic-injection-bundle/v/unstable.svg)](https://packagist.org/packages/ppokatilo/magic-injection-bundle)
[![License](https://poser.pugx.org/ppokatilo/magic-injection-bundle/license.svg)](https://packagist.org/packages/ppokatilo/magic-injection-bundle)

This Symfony2 bundle provides a way to magically inject dependencies into your services. The dependencies
will **not** be available in the constructor. To use it, you need to complete the following steps:

1. Add the tag `magic_injection.injectable_service` to the service you wish to inject. The tag takes
an optional argument called `type`, which you can use to group injectable services.
2. Add the tag
`magic_injection.injection_target` to the service which should receive the injected services.
3. Finally, annotate properties with the `MagicInjection` annotation, which will take an optional `type`
argument that refers to a group of injectable services.

Example usage
-------------
* services.yml
  ```yaml
  services:
  
    service.that.will.be.injected:
      class: Service\MyServiceA
      tags:
        - { name: magic_injection.injectable_service, type: my_services }
        
    service.that.has.a.dependency:
      class: Service\MyServiceB
      tags:
        - { name: magic_injection.injection_target }
  ```
* MyServiceB.php
  ```php
  class MyServiceB
  {
    /**
     * @MagicInjection(type="my_services")
     * @var \Service\MyServiceA
     */
    private $myServiceA;
  
    public function __construct()
    {
      assert($this->myServiceA === null);
    }
    
    public function foo()
    {
      $this->myServiceA->bar();
    }
  }
  ```
