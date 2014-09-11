<?php
/**
 * @var yii\web\View $this
 */
$this->title = '债权转让 - 旺财谷';
?>
<div class="zhutineirong">
    <?= $this->render('/includes/user/center/left_menu') ?>
    <div class="right">
        <div class="right_top2">
            <div class="tittle_02kj">债权转出</div>
            <div class="jiedaizhanghu_bottomkj">
                <ul>
                    <li>请正确填写并确认下述的各项内容</li>
                </ul>
            </div>
            <div class="jiekuantongji_kj">
                <ul>
                    <li><span>原始投资金额：</span><span id="original_invest_amt">100000.00 元</span></li>
                    <li><span>年化利率：</span><span id="original_invest_amt">10%</span></li>
                    <li><span>已收利息：</span><span id="returned_interest_amt">0.00 元</span></li>
                    <li><span>剩余期数/剩余期限：</span><span id="returned_interest_amt">98天</span></li>
                    <li><span>当前待收本息：</span><span id="returned_interest_amt">102.72元/份</span></li>
                    <li title="一般为转让时的待回收本金与应计利息之和。"><span>当前债权价值：</span><span id="returned_interest_amt">102.72元/份</span></li>
                    <li title="可转让的份数"><span>可转让份数：</span><span id="returned_interest_amt">1000 份</span></li>
                    <li title=""><span>转让份数：</span><span id="returned_interest_amt">1000</span></li>
                    <li title="折让比例，仅作用于债权待收本金部分"><span>折让比例：</span><span id="returned_interest_amt">5%</span></li>
                    <li title="折让比例，仅作用于债权待收本金部分"><span>转让价格：</span><span id="returned_interest_amt">97.58元/份</span></li>
                    <li title="折让比例，仅作用于债权待收本金部分"><span>转让总价：</span><span id="returned_interest_amt">97580.00元</span></li>
                    <li title="转让手续费是平台收取，比例为千分之五"><span>转让手续费：</span><span id="returned_interest_amt">475.00元</span></li>
                    <li title="转让手续费是平台收取，比例为千分之五"><span>预计收入金额：</span><span id="returned_interest_amt">97105.00元</span></li>
                    <li><span><button title="确定">确定</button> </span><span><button title="确定">取消</button> </span></li>
                    <div class="clear"></div>
                </ul>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <div class="clear"></div>
</div>
