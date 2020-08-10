<?php

    namespace BrokenTitan\HeaderPaginate;

    use Illuminate\Http\Resources\Json\ResourceCollection as ResourceCollection;

    class HeaderPaginateResourceCollection extends ResourceCollection {
        /**
         * Create a paginate-aware HTTP response.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\JsonResponse
         */
        protected function preparePaginatedResponse($request) {
            if ($this->preserveAllQueryParameters) {
                $this->resource->appends($request->query());
            } elseif (! is_null($this->queryParameters)) {
                $this->resource->appends($this->queryParameters);
            }

            return (new HeaderPaginateResourceResponse($this))->toResponse($request);
        }
    }
