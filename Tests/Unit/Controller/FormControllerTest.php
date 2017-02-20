<?php
namespace Smichaelsen\Settings\Tests\Unit\Controller;

use Smichaelsen\Settings\Controller\FormController;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\FormDataCompiler;
use TYPO3\CMS\Backend\Form\FormResultCompiler;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class FormControllerTest extends UnitTestCase
{

    /**
     * @var FormController
     */
    protected $subject;

    public function setUp()
    {
        $this->subject = new FormController();
    }

    /**
     * @test
     */
    public function showAction()
    {
        /** @var FormDataCompiler|\PHPUnit_Framework_MockObject_MockObject $formDataCompiler */
        $formDataCompiler = $this->createMock(FormDataCompiler::class);
        $formDataCompiler->expects($this->any())->method('compile')->willReturn([
            'databaseRow' => [],
        ]);
        /** @var AbstractNode|\PHPUnit_Framework_MockObject_MockObject $abstractNode */
        $abstractNode = $this->createMock(AbstractNode::class);
        $abstractNode->expects($this->any())->method('render')->willReturn(['html' => '']);
        /** @var NodeFactory|\PHPUnit_Framework_MockObject_MockObject $nodeFactory */
        $nodeFactory = $this->createMock(NodeFactory::class);
        $nodeFactory->expects($this->any())->method('create')->willReturn($abstractNode);

        $this->inject($this->subject, 'view', $this->createMock(ViewInterface::class));
        $this->inject($this->subject, 'pageRenderer', $this->createMock(PageRenderer::class));
        $this->inject($this->subject, 'formDataCompiler', $formDataCompiler);
        $this->inject($this->subject, 'nodeFactory', $nodeFactory);
        $this->inject($this->subject, 'formResultCompiler', $this->createMock(FormResultCompiler::class));

        $this->subject->showAction();
    }

}
