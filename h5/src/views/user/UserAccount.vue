<template>
  <div class="bg">
    <img src="http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/Icon_left_arrow.png" class="fanhui" onclick="javascript:history.go(-1)">
    <div class="yue">
      <div style="font-size: 0.5rem;">余额</div>
      <div>￥{{now_money}}</div>
    </div>

  </div>
</template>
<style>
  .fanhui {
    margin: 0.2rem;
    width: .5rem;
    height: .5rem;
  }
  .bg {
    background: url("http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/qiabao.png");
    background-size: auto;
    background-size: 100% auto;
  }
  .yue {
    padding: 1.3rem;
    color: #fff;
    text-align: center;
  }
</style>
<script>
import Recommend from "@components/Recommend";
import { getActivityStatus, getBalance } from "../../api/user";
export default {
  name: "UserAccount",
  components: {
    Recommend
  },
  props: {},
  data: function() {
    return {
      now_money: 0,
      orderStatusSum: 0,
      recharge: 0,
      activity: {
        is_bargin: false,
        is_pink: false,
        is_seckill: false
      }
    };
  },
  mounted: function() {
    this.getIndex();
    this.getActivity();
  },
  methods: {
    getIndex: function() {
      let that = this;
      getBalance().then(
        res => {
          that.now_money = res.data.now_money;
          that.orderStatusSum = res.data.orderStatusSum;
          that.recharge = res.data.recharge;
        },
        err => {
          that.$dialog.message(err.msg);
        }
      );
    },
    getActivity: function() {
      let that = this;
      getActivityStatus().then(
        res => {
          that.activity.is_bargin = res.data.is_bargin;
          that.activity.is_pink = res.data.is_pink;
          that.activity.is_seckill = res.data.is_seckill;
        },
        error => {
          that.$dialog.message(error.msg);
        }
      );
    }
  }
};
</script>
