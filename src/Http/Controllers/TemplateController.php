<?php

namespace Rjvandoesburg\NovaUrlRewriteTemplating\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Rjvandoesburg\NovaTemplating\Http\Controllers\Api\TemplateController as TemplateBaseController;
use Rjvandoesburg\NovaTemplating\TemplateHelper;
use Rjvandoesburg\NovaUrlRewrite\Contracts\UrlRewriteRepository;
use Rjvandoesburg\NovaUrlRewrite\Models\UrlRewrite;

class TemplateController extends TemplateBaseController
{
    /**
     * @var \Rjvandoesburg\NovaUrlRewrite\Contracts\UrlRewriteRepository
     */
    protected $repository;

    /**
     * TemplateController constructor.
     *
     * @param  \Rjvandoesburg\NovaUrlRewrite\Contracts\UrlRewriteRepository  $repository
     * @param  \Rjvandoesburg\NovaTemplating\TemplateHelper  $templateHelper
     */
    public function __construct(TemplateHelper $templateHelper, UrlRewriteRepository $repository)
    {
        parent::__construct($templateHelper);

        $this->repository = $repository;
    }

    /**
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resource(NovaRequest $request): \Illuminate\Http\JsonResponse
    {
        $response = parent::resource($request);

        if ($response->getStatusCode() === 404) {
            return $this->__invoke($request, $request->route('resource').'/'.$request->route('resourceId'));
        }

        return $response;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param $templateUrl
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, ?string $templateUrl = ''): \Illuminate\Http\JsonResponse
    {
        try {
            if (empty($templateUrl)) {
                return response()->json([
                    'templates' => $this->templateHelper->defaultTemplates(),
                ]);
            }

            // Check if we are dealing with a urlRewrite
            if (! $urlRewrite = $this->repository->getByRequestPath($templateUrl)) {
                return response()->json([
                    'templates' => $this->templateHelper->notFound(),
                ]);
            }

            if ($urlRewrite->isRedirect()) {
                return response()->json([
                    'redirect'   => $urlRewrite->target_path,
                    'status'     => $urlRewrite->getRedirectType(),
                    'isExternal' => parse_url($urlRewrite->target_path, PHP_URL_HOST) !== null,
                ]);
            }

            return response()->json([
                'templates' => $this->getTemplates($urlRewrite),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'templates' => $this->templateHelper->serverError(),
                'message'   => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param  \Rjvandoesburg\NovaUrlRewrite\Models\UrlRewrite  $urlRewrite
     *
     * @return array
     */
    protected function getTemplates(UrlRewrite $urlRewrite): array
    {
        if (! empty($urlRewrite->resource_type)) {
            return $this->templateHelper->forResource($urlRewrite->resource);
        }

        if ($urlRewrite->model !== null) {
            return $this->templateHelper->forModel($urlRewrite->model);
        }

        return $this->templateHelper->defaultTemplates();
    }
}
