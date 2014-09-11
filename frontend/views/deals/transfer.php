<?php
/**
 * @var yii\web\View $this
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
$this->title = '债权转让 - 旺财谷';
$data = $data ? $data : [];
$deal = isset($data['deal']) ? $data['deal'] : null;
$dealOrder = isset($data['order']) ? $data['order'] : null;
?>
<style>
    .help-block {
        display: inline;
        margin-left: 10px;
        color: red;
        text-align: right;
    }
</style>
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
            <?php $form = ActiveForm::begin(['enableClientValidation' => true]); ?>
            <div class="jiekuantongji_kj">
                <ul>
                    <li><span>原始投资金额：</span><span id="original_invest_amt"><?= $dealOrder['order_money'] ?> 元</span></li>
                    <li><span>年化利率：</span><span id="original_invest_amt"><?= $deal['syl'] ?>%</span></li>
                    <li><span>已收利息：</span><span id="returned_interest_amt"><?= $data['retrievedInterestAmt'] ?> 元</span></li>
                    <li><span>剩余期数/剩余期限：</span><span id="returned_interest_amt"><?= $data['leavingPeriod'] ?> 天</span></li>
                    <li><span>当前待收本息：</span><span id="returned_interest_amt"><?= $data['creditActualValue'] ?>元/份</span></li>
                    <li title="一般为转让时的待回收本金与应计利息之和。"><span>当前债权价值：</span><span id="returned_interest_amt"><?= $data['creditUnitValue']  ?>元/份</span></li>
                    <li title="可转让的份数"><span>可转让份数：</span><span id="returned_interest_amt"><?= $data['shares'] ?> 份</span></li>
                    <?= $form->field($model, 'transfer_shares', ['template'=>'<li><span>{label}：</span><div>{input}{error}</div></li>']) ?>
                    <?= $form->field($model, 'discount_rate', ['template'=>'<li><span>{label}：</span><div>{input} %{error}</div></li>']) ?>
                    <li title="折让比例，仅作用于债权待收本金部分"><span>转让价格：</span><span id="returned_interest_amt"><?= $data['creditUnitValue'] ?>元/份</span></li>
                    <li title="折让比例，仅作用于债权待收本金部分"><span>转让总价：</span><span id="returned_interest_amt"><?= $data['creditUnitValue'] * $data['transfer_shares'] ?>元</span></li>
                    <li title="转让手续费是平台收取，比例为千分之五"><span>转让手续费：</span><span id="returned_interest_amt"><?= $data['creditTransferFeeAmt'] ?>元</span></li>
                    <li title="转让手续费是平台收取，比例为千分之五"><span>预计收入金额：</span><span id="returned_interest_amt"><?= $data['creditorValue'] ?>元</span></li>
                    <li><?= Html::submitButton('确定', ['class' => 'jrb_but03']) ?></li>
                    <div class="clear"></div>
                </ul>
            </div>
            <?php ActiveForm::end(); ?>
            <div class="clear"></div>
        </div>
    </div>
    <div class="clear"></div>
</div>
