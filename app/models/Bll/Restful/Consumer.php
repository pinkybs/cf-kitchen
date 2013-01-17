<?php

class Bll_Restful_Consumer
{
    public static function getConsumerData($appId)
    {
        $mixi_restful_consumer = array(
            18543     => array(
                            'app_id'            => 18543,
                            'app_name'          => 'kitchen',
                            'description'       => 'kitchen test',
                            'consumer_key'      => '668d813f1ba5554bbbc3',
                            'comsumer_secret'   => '2f8226b71696efd9640ea757240207f0e5cdad00'
                        ),
            16235     => array(
                            'app_id'            => 16235,
                            'app_name'          => 'kitchen',
                            'description'       => 'kitchen',
                            'consumer_key'      => '2d152f2fcd035346cd54',
                            'comsumer_secret'   => '253927fb6c5e1ee85241234298e9eef8c00a5d03'
                        )
        );

        if (isset($mixi_restful_consumer[$appId])) {
            return $mixi_restful_consumer[$appId];
        }

        return null;
    }
}
