<?php
	// 获取首页数据
	public function indexData()
	{
	    $user_id = static::$user['uid'];

	    // 设置默认小数位
	    bcscale(2);
	    $data               = [];
	    $data['header_box'] = [
	        'title' => '首页'
	    ];

	    // 1、活动情况
	    $actions = Config::get('market_actions_map');

	    $where = [];
	    if (limited_data()) {
	        // 获取当前登录用户所属组织下 全部的账号 id
	        $allowUserId = get_user_ids_by_current_user_org();

	        $where['creator'] = ['in', $allowUserId];
	    }
	    // 精准营销
	    $where['act_status'] = [
	        'in',
	        [
	            2,
	            5
	        ]
	    ];
	    $market_list         = Db::table("{$actions[1]}")->field("'营销活动' title,1 type,act_status status,'member-marketing/activity-management' url,
	        'act_type' param,act_auditor_id auditor,creator")
	                             ->where($where)
	                             ->select();
	    // 积分兑券
	    unset($where['act_status']);
	    $where['status'] = [
	        'in',
	        [
	            2,
	            5
	        ]
	    ];
	    $ech_coup_list   = Db::table("{$actions[2]}")->field("'积分兑券' title,2 type,status,'member-marketing/integral-coupon' url,
	        'act_type' param,auditor,creator")
	                         ->where($where)
	                         ->select();
	    // 扫码领券
	    $where['status'] = [
	        'in',
	        [
	            2,
	            5
	        ]
	    ];
	    unset($where['act_status']);
	    $sweep_list = Db::table("{$actions[4]}")->field("'扫码领券' title,3 type,status,'member-marketing/scan-code' url,
	        'act_type' param,auditor,creator")
	                    ->where($where)
	                    ->select();
	    // 门店送券
	    if (isset($where['creator'])) {
	        $where['creater'] = $where['creator'];

	        unset($where['creator']);
	    }
	    unset($where['status']);

	    $where['act_status'] = [
	        'in',
	        [
	            2,
	            3
	        ]
	    ];
	    $shop_send_list      = Db::table("{$actions[5]}")->field("'门店送券' title,4 type,act_status status,'member-marketing/shop-coupon' url,
	        'act_type' param,auditor,creater creator")
	                             ->where($where)
	                             ->select();
	    // 现金红包 [不分数据权限]
	    if (isset($where['creater'])) {
	        // $where['creator'] = $where['creater'];
	        unset($where['creater']);
	    }
	    unset($where['act_status']);
	    $where['status'] = [
	        'in',
	        [
	            2,
	            5
	        ]
	    ];
	    $crash_list      = Db::table("{$actions[6]}")->field("'现金红包' title,5 type,status,'member-marketing/cash-redpacket' url,
	        'act_type' param,auditor,creator")
	                         ->where($where)
	                         ->select();

	    // 活动数据
	    $act_list = array_merge($market_list, $ech_coup_list, $sweep_list, $shop_send_list, $crash_list);
	    // 待办事项 默认数据
	    $act_lists = [
	        'to_do' => [
	            'title' => lang('to_do'),
	            'data'  => [
	                [
	                    'title' => lang('activities_to_be_audited'),
	                    'value' => 0
	                ]
	            ],
	            'list'  => []
	        ],
	        'on_go' => [
	            'title' => lang('marketing_activities'),
	            'data'  => [
	                1 => [
	                    'type'   => 1,
	                    'title'  => lang('marketing_activities'), // 精准营销
	                    'icon'   => 'icon-huiyuanyingxiao-xuanzhong',
	                    'value'  => 0,
	                    'url'    => 'member-marketing/activity-management',
	                    'params' => [
	                        'field' => 'act_status',
	                        'value' => 5,
	                        'type'  => static::FILTER_TYPE
	                    ]
	                ],
	                2 => [
	                    'type'   => 2,
	                    'title'  => lang('integral_ech_coupons'), // 积分兑券
	                    'icon'   => 'icon-jifenduiquan',
	                    'value'  => 0,
	                    'url'    => 'member-marketing/integral-coupon',
	                    'params' => [
	                        'field' => 'status',
	                        'value' => 5,
	                        'type'  => static::FILTER_TYPE
	                    ]
	                ],
	                3 => [
	                    'type'   => 3,
	                    'title'  => lang('sweep_send_coup'), // 扫码领券
	                    'icon'   => 'icon-saomalingquan',
	                    'value'  => 0,
	                    'url'    => 'member-marketing/scan-code',
	                    'params' => [
	                        'field' => 'status',
	                        'value' => 5,
	                        'type'  => static::FILTER_TYPE
	                    ]
	                ],
	                4 => [
	                    'type'   => 4,
	                    'title'  => lang('shop_send_coupon'), // 门店送券
	                    'icon'   => 'icon-mendiansongquan',
	                    'value'  => 0,
	                    'url'    => 'member-marketing/shop-coupon',
	                    'params' => [
	                        'field' => 'act_status',
	                        'value' => 3,
	                        'type'  => static::FILTER_TYPE
	                    ]
	                ],
	                5 => [
	                    'type'   => 5,
	                    'title'  => lang('crash_rp'), // 现金红包
	                    'icon'   => 'icon-xianjinhongbao',
	                    'value'  => 0,
	                    'url'    => 'member-marketing/cash-redpacket',
	                    'params' => [
	                        'field' => 'status',
	                        'value' => 5,
	                        'type'  => static::FILTER_TYPE
	                    ]
	                ],
	                7 => [
	                    'type'  => 7,
	                    'title' => lang('gift_card'), // 礼品卡
	                    'icon'  => 'icon-lipinqia',
	                    'value' => 0,
	                    'url'   => 'member-marketing/giftcard-management',
	                    'val'   => 3
	                ]
	            ]
	        ]
	    ];
	    if (!empty($act_list)) {
	        foreach ($act_list as $k => $item) {
	            if ($item['status'] == 2) {
	                if ($item['auditor'] != $user_id) {
	                    continue;
	                }
	                switch ($item['type']) {
	                    case 1:
	                        $markets[]                                 = $item;
	                        $act_lists['to_do']['list'][$item['type']] = [
	                            'title' => $item['title'],
	                            'value' => isset($markets) ? count($markets) : 0,
	                            'url'   => $item['url'],
	                            'val'   => 2
	                        ];
	                        break;
	                    case 2:
	                        $ech_coup[]                                = $item;
	                        $act_lists['to_do']['list'][$item['type']] = [
	                            'title' => $item['title'],
	                            'value' => isset($ech_coup) ? count($ech_coup) : 0,
	                            'url'   => $item['url'],
	                            'val'   => 2
	                        ];
	                        break;
	                    case 3:
	                        $sweep_coup[]                              = $item;
	                        $act_lists['to_do']['list'][$item['type']] = [
	                            'title' => $item['title'],
	                            'value' => isset($sweep_coup) ? count($sweep_coup) : 0,
	                            'url'   => $item['url'],
	                            'val'   => 2
	                        ];
	                        break;
	                    case 4:
	                        $shop_coup[]                               = $item;
	                        $act_lists['to_do']['list'][$item['type']] = [
	                            'title' => $item['title'],
	                            'value' => isset($shop_coup) ? count($shop_coup) : 0,
	                            'url'   => $item['url'],
	                            'val'   => 2
	                        ];
	                        break;
	                    case 5:
	                        $crashes[]                                 = $item;
	                        $act_lists['to_do']['list'][$item['type']] = [
	                            'title' => $item['title'],
	                            'value' => isset($crashes) ? count($crashes) : 0,
	                            'url'   => $item['url'],
	                            'val'   => 2
	                        ];
	                        break;
	                    default:
	                        break;
	                }
	            } else {
	                /*
	                 * if ($item['creator'] != $user_id) {
	                 * continue;
	                 * }
	                 */
	                $num[$item['type']][]                               = $item;
	                $act_lists['on_go']['data'][$item['type']]['value'] = count($num[$item['type']]);
	                $act_lists['on_go']['data'][$item['type']]['val']   = 1;
	            }
	        }
	    }
	    array_multisort($act_lists['to_do']['list']);
	    $act_lists['to_do']['data'][0]['value'] = array_sum(array_column($act_lists['to_do']['list'], 'value'));
	    array_multisort($act_lists['on_go']['data']);

	    // 公共时间范围
	    $start_day = date('Y-m-d 00:00:00', strtotime("-30 day")); // 开始时间
	    $end_day   = date('Y-m-d 23:59:59', strtotime('-1 day')); // 结束时间

	    // 3、收入情况 需要整理一下怎么计算的
	    // 3.1 当月收入总览
	    // 当前查看视角条件获取、补充

	    // 最近30天
	    $service_income = new \app\analysis\model\OverallIncomeAnalysis();
	    $last_income = $service_income->getIncomeTotal($start_day, $end_day);

	    // 同期
	    $year_start  = date('Y-m-d H:i:s', strtotime("{$start_day} -1 year"));
	    $year_end    = date('Y-m-d H:i:s', strtotime("{$end_day} -1 year"));
	    $year_income = $service_income->getIncomeTotal($year_start, $year_end);
	    // 上期
	    $before_start  = date('Y-m-d H:i:s', strtotime("{$start_day} -30 day"));
	    $before_end    = date('Y-m-d H:i:s', strtotime("{$end_day} -30 day"));
	    $before_income = $service_income->getIncomeTotal($before_start, $before_end);
	    // 默认数据
	    $income_info      = [
	        [
	            'source' => 1,
	            'field'  => 'order_num',
	            'title'  => '订单笔数（笔）',
	            'value'  => $last_income['order_num'],
	            'tip1'   => '同期',
	            'value1' => $year_income['order_num'],
	            'tip2'   => '上期',
	            'value2' => $before_income['order_num'],
	        ],
	        [
	            'source' => 2,
	            'field'  => 'custom_price',
	            'title'  => '客单价（元）',
	            'value'  => $last_income['custom_price'],
	            'tip1'   => '同期',
	            'value1' => $year_income['custom_price'],
	            'tip2'   => '上期',
	            'value2' => $before_income['custom_price'],
	        ],
	        [
	            'source' => 3,
	            'field'  => 'custom_trade',
	            'title'  => '客单件（件）',
	            'value'  => $last_income['custom_trade'],
	            'tip1'   => '同期',
	            'value1' => $year_income['custom_trade'],
	            'tip2'   => '上期',
	            'value2' => $before_income['custom_trade'],
	        ],
	        [
	            'source' => 4,
	            'field'  => 'goods_price',
	            'title'  => '件单价（元）',
	            'value'  => $last_income['goods_price'],
	            'tip1'   => '同期',
	            'value1' => $year_income['goods_price'],
	            'tip2'   => '上期',
	            'value2' => $before_income['goods_price'],
	        ],
	    ];
	    $income           = [
	        'title'      => lang('income_condition') . '（近30天）',
	        'select_tab' => [
	            'type'  => '',
	            'field' => '',
	            'data'  => $income_info
	        ]
	    ];
	    $income['echars'] = [];

	    $data['body_box'] = array_merge($act_lists, [
	        'income_condition' => $income,
	    ]);
	    $data['attrs']    = [];

	    return $data;
	}
