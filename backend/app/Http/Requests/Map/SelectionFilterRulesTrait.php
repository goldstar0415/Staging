<?php

namespace App\Http\Requests\Map;

trait SelectionFilterRulesTrait {
    protected function getFilterRules() {
        $rules = [
            'filter.type'         => 'string',
            'filter.start_date'   => 'date_format:Y-m-d',
            'filter.end_date'     => 'date_format:Y-m-d',
            'filter.category_ids' => 'array',
            'filter.tags'         => 'array',
            'filter.rating'       => 'integer',
            'filter.price'        => 'integer',
        ];


        // this is only to be used by a Map/Request which has arrayFieldRules
        $rules = array_merge($rules, $this->arrayFieldRules('category_ids', 'required|integer'));
        $rules = array_merge($rules, $this->arrayFieldRules('tags', 'required|string'));

        return $rules;
    }
}

