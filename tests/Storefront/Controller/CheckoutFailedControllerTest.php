<?php

declare(strict_types=1);

namespace Axytos\DecisionExpert\Shopware\Tests;

use Axytos\DecisionExpert\Shopware\Storefront\Controller\CheckoutFailedController;
use Axytos\DecisionExpert\Shopware\Storefront\Controller\StorefrontViewRenderer;
use Axytos\Shopware\ErrorReporting\ErrorHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\ErrorController;
use Shopware\Storefront\Page\GenericPageLoader;
use Shopware\Storefront\Page\Page;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckoutFailedControllerTest extends TestCase
{
    /** @var GenericPageLoader&MockObject */
    private GenericPageLoader $genericPageLoader;

    /** @var StorefrontViewRenderer&MockObject */
    private StorefrontViewRenderer $storefrontViewRenderer;

    /** @var ErrorController&MockObject */
    private ErrorController $errorController;

    /** @var ErrorHandler&MockObject */
    private ErrorHandler $errorHandler;

    private CheckoutFailedController $sut;

    public function setUp(): void
    {
        $this->genericPageLoader = $this->createMock(GenericPageLoader::class);
        $this->storefrontViewRenderer = $this->createMock(StorefrontViewRenderer::class);
        $this->errorController = $this->createMock(ErrorController::class);
        $this->errorHandler = $this->createMock(ErrorHandler::class);

        $this->sut = new CheckoutFailedController(
            $this->genericPageLoader,
            $this->storefrontViewRenderer,
            $this->errorController,
            $this->errorHandler
        );
    }

    public function test_failed_renders_checkout_failed_view(): void
    {
        $request = $this->createMock(Request::class);
        $context = $this->createMock(SalesChannelContext::class);
        $page = $this->createMock(Page::class);
        $response = $this->createMock(Response::class);

        $this->genericPageLoader
            ->method('load')
            ->with($request, $context)
            ->willReturn($page);

        $this->storefrontViewRenderer
            ->method('renderStoreFrontView')
            ->with(CheckoutFailedController::CHECKOUT_FAILED_VIEW, [
                'page' => $page
            ])
            ->willReturn($response);

        $actual = $this->sut->failed($request, $context);

        $this->assertSame($response, $actual);
    }

    public function test_failed_delegates_to_error_controller_if_page_load_fails(): void
    {
        $request = $this->createMock(Request::class);
        $context = $this->createMock(SalesChannelContext::class);
        $exception = new \Exception();
        $response = $this->createMock(Response::class);

        $this->genericPageLoader->method('load')->willThrowException($exception);
        $this->errorController->method('error')->willReturn($response);

        $actual = $this->sut->failed($request, $context);

        $this->assertSame($response, $actual);
    }

    public function test_failed_reports_error_if_page_load_fails(): void
    {
        $request = $this->createMock(Request::class);
        $context = $this->createMock(SalesChannelContext::class);
        $exception = new \Exception();
        $response = $this->createMock(Response::class);

        $this->genericPageLoader->method('load')->willThrowException($exception);
        $this->errorController->method('error')->willReturn($response);

        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($exception);

        $this->sut->failed($request, $context);
    }
}
