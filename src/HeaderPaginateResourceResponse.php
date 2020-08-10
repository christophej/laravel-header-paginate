<?php

    namespace BrokenTitan\HeaderPaginate;

    use Illuminate\Support\Arr;
    use Illuminate\Http\Resources\Json\ResourceResponse as ResourceResponse;

    class HeaderPaginateResourceResponse extends ResourceResponse {
        /**
         * Create an HTTP response that represents the object.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\JsonResponse
         */
        public function toResponse($request) {
            return parent::toResponse($request)->withHeaders($this->paginationInformation($request));
            return tap(response()->json(
                $this->wrap(
                    $this->resource->resolve($request),
                    array_merge_recursive(
                        $this->paginationInformation($request),
                        $this->resource->with($request),
                        $this->resource->additional
                    )
                ),
                $this->calculateStatus()
            ), function ($response) use ($request) {
                $response->original = $this->resource->resource->map(function ($item) {
                    return is_array($item) ? Arr::get($item, 'resource') : $item->resource;
                });

                $this->resource->withResponse($request, $response);
            });
        }

        /**
         * Add the pagination information to the response.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return array
         */
        protected function paginationInformation($request) {
            $paginated = $this->resource->resource->toArray();

            return [
                'Link' => $this->paginationLinks($paginated)
            ];
        }

        /**
         * Get the pagination links for the response. Parses them in to RFC 5988 format.
         *
         * @param  array  $paginated
         * @return array
         */
        protected function paginationLinks($paginated) {
            $links = [
                'first' => $paginated['first_page_url'] ?? null,
                'last' => $paginated['last_page_url'] ?? null,
                'prev' => $paginated['prev_page_url'] ?? null,
                'next' => $paginated['next_page_url'] ?? null,
            ];
            $links = array_filter($links, 'strlen');
            $links = array_map(function($link, $key) {
                if (!empty($link)) {
                    return "<{$link}>; rel=\"{$key}\"";
                }
            }, $links, array_keys($links));
            $links = implode(", ", $links);

            return $links;
        }

        /**
         * Gather the meta data for the response.
         *
         * @param  array  $paginated
         * @return array
         */
        protected function meta($paginated) {
            return Arr::except($paginated, [
                'data',
                'first_page_url',
                'last_page_url',
                'prev_page_url',
                'next_page_url',
            ]);
        }
    }
