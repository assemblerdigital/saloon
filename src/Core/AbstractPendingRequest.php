<?php

namespace Saloon\Core;

use Saloon\Config;
use Saloon\Helpers\Helpers;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Macroable;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Saloon\Contracts\FakeResponse;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Middleware\DelayMiddleware;
use Saloon\Http\PendingRequest\BootPlugins;
use Saloon\Http\Middleware\ValidateProperties;
use Saloon\Http\Middleware\DetermineMockResponse;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\PendingRequest\BootConnectorAndRequest;
use Saloon\Traits\RequestProperties\HasMiddleware;
// I have moved the authentication stuff back into the base class, but I'm not sure if this is the right move long-term.
use Saloon\Contracts\Authenticator;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Http\PendingRequest\AuthenticatePendingRequest;



abstract class AbstractPendingRequest
{
    use Conditionable;
    use HasMockClient;
    use Macroable;
    use AuthenticatesRequests;
    use HasMiddleware; // I'm attempting to extract the Middleware component back into the Core namespace to see if it can be more generally useful.

    /**
     * The connector making the request.
     */
    protected Connector $connector;

    /**
     * The request used by the instance.
     */
    protected Request $request;

    /**
     * The simulated response.
     */
    protected ?FakeResponse $fakeResponse = null;

    final public function __construct(Connector $connector, Request $request, ?MockClient $mockClient = null)
    {
        $this->bootstrap(...func_get_args());
        $this->make(...func_get_args());

        // Finally, we will execute the request middleware pipeline which will
        // process the middleware in the order we added it.

        $this->middleware()->executeRequestPipeline($this);
    }

    private function bootstrap(Connector $connector, Request $request, ?MockClient $mockClient = null): void
    {
        $this->connector = $connector;
        $this->request = $request;
        $this->authenticator = $request->getAuthenticator() ?? $connector->getAuthenticator();
        $this->mockClient = $mockClient ?? $request->getMockClient() ?? $connector->getMockClient() ?? MockClient::getGlobal();

        // Now, we'll register our global middleware and our mock response middleware.
        // Registering these middleware first means that the mock client can set
        // the fake response for every subsequent middleware.

        $this->middleware()->merge(Config::globalMiddleware());
        $this->middleware()->onRequest(new DetermineMockResponse, 'determineMockResponse');

        // Next, we'll boot our plugins. These plugins can add headers, config variables and
        // even register their own middleware. We'll use a tap method to simply apply logic
        // to the PendingRequest. After that, we will merge together our request properties
        // like headers, config, middleware, body and delay, and we'll follow it up by
        // invoking our authenticators. We'll do this here because when middleware is
        // executed, the developer will have access to any headers added by the middleware.

        $this
            ->tap(new BootPlugins)
            ->tap(new AuthenticatePendingRequest)
            ->tap(new BootConnectorAndRequest);

        // Now, we'll register some default middleware for validating the request properties and
        // running the delay that should have been set by the user.

        $this->middleware()
            ->onRequest(new ValidateProperties, 'validateProperties')
            ->onRequest(new DelayMiddleware, 'delayMiddleware');



    }

    abstract public function make(Connector $connector, Request $request, ?MockClient $mockClient = null): void;

    /**
     * Get the request.
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get the connector.
     */
    public function getConnector(): Connector
    {
        return $this->connector;
    }


    /**
     * Authenticate the PendingRequest
     *
     * @return $this
     */
    public function authenticate(Authenticator $authenticator): static
    {
        $this->authenticator = $authenticator;

        // Since the PendingRequest has already been constructed we will run the set
        // method on the authenticator which runs it straight away.

        $this->authenticator->set($this);

        return $this;
    }


    /**
     * Execute the response pipeline.
     */
    public function executeResponsePipeline(Response $response): Response
    {
        return $this->middleware()->executeResponsePipeline($response);
    }

    /**
     * Execute the fatal pipeline.
     */
    public function executeFatalPipeline(FatalRequestException $throwable): void
    {
        $this->middleware()->executeFatalPipeline($throwable);
    }

    /**
     * Get the fake response
     */
    public function getFakeResponse(): ?FakeResponse
    {
        return $this->fakeResponse;
    }

    /**
     * Set the fake response
     *
     * @return $this
     */
    public function setFakeResponse(?FakeResponse $fakeResponse): static
    {
        $this->fakeResponse = $fakeResponse;

        return $this;
    }

    /**
     * Check if a fake response has been set
     */
    public function hasFakeResponse(): bool
    {
        return $this->fakeResponse instanceof FakeResponse;
    }

    /**
     * Get the response class
     *
     * @return class-string<\Saloon\Http\Response>
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     */
    public function getResponseClass(): string
    {
        $response = $this->request->resolveResponseClass() ?? $this->connector->resolveResponseClass() ?? Response::class;

        if (! class_exists($response) || ! Helpers::isSubclassOf($response, Response::class)) {
            throw new InvalidResponseClassException;
        }

        return $response;
    }


    /**
     * Tap into the pending request
     *
     * @return $this
     */
    protected function tap(callable $callable): static
    {
        $callable($this);

        return $this;
    }

}
