<?php
	// 获取首页数据
	public function indexData()
	{
	    // 设置默认小数位
	    bcscale(2);
	    $data               = [];
	    $data['header_box'] = [
	        'title' => '首页'
	    ];

	    // 活动列表
	    $act_list = $this->getActivityList();

	    // 待办事项、营销活动默认数据
	    $default_data = $this->getActivityDefaultData();

	    $merge_data = $this->getActivityMergeData($act_list, $default_data);
	    unset($act_list);
	    unset($default_data);

	    array_multisort($merge_data['to_do']['list']);
	    $merge_data['to_do']['data'][0]['value'] = array_sum(array_column($merge_data['to_do']['list'], 'value'));
	    array_multisort($merge_data['on_go']['data']);

	    // 收入情况
	    $income = $this->getIncomeData();

	    $data['body_box'] = array_merge($merge_data, [
	        'income_condition' => $income,
	    ]);
	    $data['attrs']    = [];

	    return $data;
	}

	// 获取活动列表数据
	public function getActivityList()
	{
	    $where = [];

	    $actions = Config::get('market_actions_map');

	    // 获取当前登录用户所属组织下 全部的账号 id
	    if (limited_data()) {
	        $allowUserId = get_user_ids_by_current_user_org();

	        $where['creator'] = ['in', $allowUserId];
	    }

	    // 精准营销
	    $market_list = $this->precisionMarketing($actions[1], $where);

	    // 积分兑券
	    $ech_coup_list = $this->integralExchangeCoupon($actions[2], $where);

	    // 扫码领券
	    $sweep_list = $this->sweepReceiveCoupon($actions[4], $where);

	    // 门店送券
	    $shop_send_list = $this->shopHandselCoupon($actions[5], $where);

	    // 现金红包 [不分数据权限]
	    $cash_list = $this->cashRedPacket($actions[6], $where);

	    return array_merge($market_list, $ech_coup_list, $sweep_list, $shop_send_list, $cash_list);
	}

	// 精准营销
	public function precisionMarketing($table_name, $where)
    {
        $where['act_status'] = ['in', [ACTIVITY_STATUS_WAIT_REVIEW, ACTIVITY_STATUS_PROCESSING]];

        return Db::table("{$table_name}")
            ->field("'营销活动' title,1 type,act_status status,'member-marketing/activity-management' url, 'act_type' param,act_auditor_id auditor,creator")
            ->where($where)
            ->select() ?? [];
    }

    // 积分兑券
    public function integralExchangeCoupon($table_name, $where)
    {
        if (!empty($where['act_status'])) {
            unset($where['act_status']);
        }
        $where['status'] = ['in', [ACTIVITY_STATUS_WAIT_REVIEW, ACTIVITY_STATUS_PROCESSING]];

        return Db::table("{$table_name}")
            ->field("'积分兑券' title,2 type,status,'member-marketing/integral-coupon' url, 'act_type' param,auditor,creator")
            ->where($where)
            ->select() ?? [];
    }

    // 扫码领券
    public function sweepReceiveCoupon($table_name, $where)
    {
        if (!empty($where['act_status'])) {
            unset($where['act_status']);
        }
        $where['status'] = ['in', [ACTIVITY_STATUS_WAIT_REVIEW, ACTIVITY_STATUS_PROCESSING]];

        return Db::table("{$table_name}")
            ->field("'扫码领券' title,3 type,status,'member-marketing/scan-code' url, 'act_type' param,auditor,creator")
            ->where($where)
            ->select() ?? [];
    }

    // 门店送券
    public function shopHandselCoupon($table_name, $where)
    {
        if (!empty($where['creator'])) {
            $where['creater'] = $where['creator'];

            unset($where['creator']);
        }
        if (!empty($where['status'])) {
            unset($where['status']);
        }

        $where['act_status'] = ['in', [ACTIVITY_STATUS_WAIT_REVIEW, ACTIVITY_STATUS_NOT_PASSED]];

        return Db::table("{$table_name}")
            ->field("'门店送券' title,4 type,act_status status,'member-marketing/shop-coupon' url, 'act_type' param,auditor,creater creator")
            ->where($where)
            ->select() ?? [];
    }

    // 现金红包
    public function cashRedPacket($table_name, $where)
    {
        if (!empty($where['creater'])) {
            unset($where['creater']);
        }
        if (!empty($where['act_status'])) {
            unset($where['act_status']);
        }

        $where['status'] = ['in', [ACTIVITY_STATUS_WAIT_REVIEW, ACTIVITY_STATUS_PROCESSING]];

        return Db::table("{$table_name}")
            ->field("'现金红包' title,5 type,status,'member-marketing/cash-redpacket' url, 'act_type' param,auditor,creator")
            ->where($where)
            ->select() ?? [];
    }

    // 获取活动默认数据
	public function getActivityDefaultData()
	{
	    return [
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
	}

	/**
	 * 合并所有活动数据（待审核活动、营销活动）
	 *
	 * 逻辑拆分
	 * 修改变量名称（之前的太相似，可读性比较差）
	 * 多条件时增加解释性变量
	 * 数据库字段数字转化成常量
	 * 代码执行效率
	 */
	public function getActivityMergeData($act_list, $default_data)
	{
		$to_do = []; // 待审核活动
	    $on_go = []; // 营销活动

		// 只有管理员可以审核，只有管理员账号可以看到审核活动
	    $user_id    = get_user_id();
	    $limit_data = not_limited_data();

	    foreach ($act_list as $k => $item) {
	        if ($item['status'] == ACTIVITY_STATUS_WAIT_REVIEW) {
	            $limit = $limit_data && $item['auditor'] == $user_id;

	            if ($limit) {
	                $to_do[$item['type']][] = $item;
	            }

	            continue;
	        }

	        $on_go[$item['type']][] = $item;
	    }

	    if (!empty($to_do)) {
	        foreach ($to_do as $type => $item) {
	            $default_data['to_do']['list'][$type] = [
	                'title' => $to_do[$type][0]['title'],
	                'value' => count($to_do[$type] ?? []),
	                'url'   => $to_do[$type][0]['url'],
	                'val'   => 2
	            ];
	        }
	    }

	    if (!empty($on_go)) {
	        foreach ($on_go as $type => $item) {
	            $default_data['on_go']['data'][$type]['value'] = count($on_go[$type] ?? []);
	            $default_data['on_go']['data'][$type]['val']   = 1;
	        }
	    }

	    return $default_data;
	}

	// 获取首页收入数据
	public function getIncomeData()
	{
	    // 公共时间范围
	    $start_day = date('Y-m-d 00:00:00', strtotime("-30 day")); // 开始时间
	    $end_day   = date('Y-m-d 23:59:59', strtotime('-1 day')); // 结束时间

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
	    $income_info = [
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

	    return $income;
	}
