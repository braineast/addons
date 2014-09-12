drop table if exists `cp_credit`;
create table if not exists `cp_credit`
(
  id int(11) unsigned not null auto_increment primary key,
  deal_id int unsigned not null comment '原始的标的id',
  deal_end_date char(10) not null comment '标的到期时间年-月-日形式',
  leaving_period int unsigned not null comment '标的剩余期限，一律以天数表示',
  user_id int(11) unsigned not null comment '订单所属用户',
  order_id char(32) not null comment '转债权的订单id',
  repayment_term_id int unsigned not null default 0 comment '当前正处在的还款期数',
  shares int unsigned not null comment '总可转的份数',
  transfer_shares int unsigned not null comment '计划转出的份数',
  in_stock_shares int unsigned not null default 0 comment '库存份数，即可售份数',
  discount_rate decimal(12, 2) not null default 0.00 comment '折让率,一般是0到百分之五间，折让率只针对每份债权中的本金部分进行计算',
  unit_principal_amt decimal(12, 6) not null comment '每份中的本金金额',
  unit_interest_amt decimal(12, 6) not null comment '每份债权应得利息，用于投资n份债权，获得n乘以此项数据的收益计算',
  unit_accrued_interest_amt decimal(12, 6) not null comment '每份中的应计利息金额，购买债权时，购买人像转让人支付的利息金额',
  actual_unit_value decimal(12, 6) not null comment '每份实际价值，即应收本息',
  unit_value decimal(12, 6) not null comment '债权价值，即unit_principal_amt与unit_accrued_interest_amt的合计',
  unit_price decimal(12, 6) not null comment '售卖价格，即unit_value减去转让人对该债权的折让金额后的数据',
  unit_incoming_amt decimal(12, 6) not null comment '每份债权的实际收入金额',
  transfer_fee_rate decimal(12, 3) not null default 0.005 comment '转让手续费率，默认为千五',
  unit_fee_amt decimal(12, 6) not null default 0.000000 comment '每份手续费金额',
  status tinyint not null default 0 comment '0=>正在转让, 1=>转让完成',
  is_deleted tinyint not null default 0,
  is_canceled tinyint not null default 0,
  updated_at int(11) unsigned not null,
  created_at int(11) unsigned not null
);

drop table if exists `cp_credit_order`;
create table if not exists `cp_credit_order`
(
  id int(11) unsigned not null auto_increment primary key,
  credit_id int unsigned not null,
  deal_id int unsigned not null,
  user_id int unsigned not null,
  serial char(32) not null,
  shares int unsigned not null comment '购买份数',
  unit_value decimal(12, 6) not null,
  discount_rate decimal(12, 4) not null default 0.0000 comment '折让率,一般是0到百分之五间',
  principal_amt decimal(12, 6) not null comment '每份中的本金金额',
  accrued_interest_amt decimal(12, 6) not null comment '每份中的应计利息金额',
  earning_rate decimal(12, 4) not null default 0.0000,
  amount decimal(12, 4) not null,
  fee decimal(12, 4) not null default 0.0000,
  status tinyint not null default 0 comment '0=>未支付, 1=>已支付',
  updated_at int(11) unsigned not null,
  created_at int(11) unsigned not null
);

-- Credit Order Detail
drop table if exists `credit_order_detail`;
create table if not exists `credit_order_detail`
(
  id int unsigned not null auto_increment primary key ,
  order_id int unsigned not null ,
  shares int unsigned not null,
  updated_at int(11) unsigned not null,
  created_at int(11) unsigned not null
);