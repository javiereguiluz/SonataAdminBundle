<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Tests\Bridge\Exporter;

use Exporter\Exporter;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class AdminExporterTest extends PHPUnit_Framework_TestCase
{
    public function provideExportFormats()
    {
        return array(
            'no override' => array(array('xls'), array('json', 'xml', 'csv', 'xls'), array('xls')),
            'override in admin' => array(array('csv'), array('csv'), array('xls')),
        );
    }

    /**
     * @dataProvider provideExportFormats
     */
    public function testAdminHasPriorityOverGlobalSettings(array $expectedFormats, array $adminFormats, array $globalFormats)
    {
        $writers = array();
        foreach ($globalFormats as $exportFormat) {
            $writer = $this->createMock('Exporter\Writer\TypedWriterInterface');
            $writer->expects($this->once())
                ->method('getFormat')
                ->will($this->returnValue($exportFormat));
            $writers[] = $writer;
        }

        $exporter = new Exporter($writers);
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())
            ->method('getExportFormats')
            ->will($this->returnValue($adminFormats));
        $adminExporter = new AdminExporter($exporter);
        $this->assertSame($expectedFormats, $adminExporter->getAvailableFormats($admin));
    }

    public function testGetExportFilename()
    {
        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue('MyProject\AppBundle\Model\MyClass'));
        $adminExporter = new AdminExporter(new Exporter());
        $this->assertRegexp(
            '#export_myclass_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}.csv#',
            $adminExporter->getExportFilename($admin, 'csv')
        );
    }
}
