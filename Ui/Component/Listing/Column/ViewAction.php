<?php

namespace WMZ\DeleteOrder\Ui\Component\Listing\Column;

class ViewAction extends \Magento\Sales\Ui\Component\Listing\Column\ViewAction
{
    const URL_PATH_DELETE = 'delete/order/index';

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'entity_id';
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                $viewUrlPath,
                                [
                                    $urlEntityParamName => $item['entity_id']
                                ]
                            ),
                            'label' => __('View')
                        ],
                        'delete' => [

                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    $urlEntityParamName => $item['entity_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete Order'),
                                'message' => __('Are you sure you wan\'t to delete the record?')
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
