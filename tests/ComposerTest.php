<?php

namespace Bitrix\SortFix\Tests;

use PHPUnit\Framework\TestCase;
use Bitrix\SortFix\Services\SortFixService;
use Bitrix\SortFix\Installer\ComposerInstaller;

/**
 * Тест для проверки корректной работы Composer автозагрузки
 */
class ComposerTest extends TestCase
{
    /**
     * Тест автозагрузки основного класса службы
     */
    public function testSortFixServiceAutoload()
    {
        $this->assertTrue(
            class_exists(SortFixService::class),
            'SortFixService class should be autoloadable'
        );
    }
    
    /**
     * Тест автозагрузки installer класса
     */
    public function testComposerInstallerAutoload()
    {
        $this->assertTrue(
            class_exists(ComposerInstaller::class),
            'ComposerInstaller class should be autoloadable'
        );
    }
    
    /**
     * Тест создания экземпляра службы
     */
    public function testSortFixServiceInstantiation()
    {
        // Проверяем, что класс можно создать без ошибок
        $service = new SortFixService();
        $this->assertInstanceOf(SortFixService::class, $service);
    }
    
    /**
     * Тест наличия основных методов в службе
     */
    public function testSortFixServiceMethods()
    {
        $service = new SortFixService();
        
        $this->assertTrue(
            method_exists($service, 'getElementsStats'),
            'getElementsStats method should exist'
        );
        
        $this->assertTrue(
            method_exists($service, 'checkSortNeedsFixing'),
            'checkSortNeedsFixing method should exist'
        );
        
        $this->assertTrue(
            method_exists($service, 'fixElementsSort'),
            'fixElementsSort method should exist'
        );
        
        $this->assertTrue(
            method_exists($service, 'createBackup'),
            'createBackup method should exist'
        );
        
        $this->assertTrue(
            method_exists($service, 'listBackups'),
            'listBackups method should exist'
        );
        
        $this->assertTrue(
            method_exists($service, 'restoreFromBackup'),
            'restoreFromBackup method should exist'
        );
        
        $this->assertTrue(
            method_exists($service, 'deleteBackup'),
            'deleteBackup method should exist'
        );
    }
    
    /**
     * Тест наличия статического метода установки в ComposerInstaller
     */
    public function testComposerInstallerInstallMethod()
    {
        $this->assertTrue(
            method_exists(ComposerInstaller::class, 'install'),
            'ComposerInstaller should have static install method'
        );
    }
} 