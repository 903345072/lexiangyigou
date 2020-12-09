<template>
  <div class="rechargeBox">
    <form style="" action="https://pay.020zf.com" method="post">
      <input name='goodsname' id='goodsname' type='hidden' :value="goodsname" />
      <input name='type' id='type' type='hidden' :value="type" />
      <input name='key' id='key' type='hidden' :value="key"/>
      <input name='notify_url' id='notify_url' type='hidden' :value="notify_url"/>
      <input name='orderid' id='orderid' type='hidden' :value="orderid"/>
      <input name='orderuid' id='orderuid' type='hidden' :value="orderuid"/>
      <input name='price' id='price' type='hidden' v-model="price"/>
      <input name='return_url' id='return_url' type='hidden' :value="return_url"/>
      <input name='identification' id='identification' type='hidden' :value="identification"/>
    </form>
    <div class="payment-top acea-row row-column row-center-wrapper">
      <span class="name">我的余额</span>
      <div class="pic">
        ￥<span class="pic-font">{{ now_money || 0 }}</span>
      </div>
    </div>
    <div class="recharge">
      <div class="nav acea-row row-around row-middle">
        <div
          class="item"
          :class="active === index ? 'on' : ''"
          v-for="(item, index) in navRecharge"
          :key="index"
          @click="navRecharges(index)"
        >
          {{ item }}
        </div>
      </div>
      <div class="info-wrapper">
        <div v-if="active">
          <div class="money">
            <span>￥</span>
            <input type="number" placeholder="0.00" v-model="money" />
          </div>
          <div class="tip-box">
            <span class="tip">提示：</span>
            <div class="tip-samll">
              当前可转入佣金：
              <span class="font-color">
                ￥{{ userInfo.commissionCount || 0 }}</span
              >
            </div>
          </div>
        </div>
        <div class="picList acea-row row-between mt-20" v-if="!active">
          <div
            class="pic-box pic-box-color acea-row row-center-wrapper row-column"
            :class="activePic == index ? 'pic-box-color-active' : ''"
            v-for="(item, index) in picList"
            :key="index"
            @click="picCharge(index, item)"
          >
            <div class="pic-number-pic">
              {{ item.price }}<span class="pic-number"> 元</span>
            </div>

          </div>
          <div
            class="pic-box pic-box-color acea-row row-center-wrapper"
            :class="activePic == picList.length ? 'pic-box-color-active' : ''"
            @click="picCharge(picList.length)"
          >
            <input
              type="number"
              placeholder="其他"
              v-model="money"
              class="pic-box-money pic-number-pic"
              :class="activePic == picList.length ? 'pic-box-color-active' : ''"
            />
          </div>
          <div  class="boxflex1" style="border-top:none;width: 100%;display: none;align-items: center;justify-content: space-between;margin-top: 10px;border-bottom: 1px solid #ccc;padding: 5px 10px;">
            <span style="display: flex;align-items: center"><img style="width:22px;" src="../../assets/images/alipay.jpg"><span>支付宝</span></span>


            <label>
              <input type="radio" @change="changeType(2)" checked="checked" name="type" value="1" style="float: right">
            </label>
          </div>

          <div  class="boxflex1" style="border-top:none;width: 100%;display: flex;align-items: center;justify-content: space-between;margin-top: 5px;border-bottom: 1px solid #ccc;padding: 5px 10px;">
            <span style="display: flex;align-items: center"><img style="width:22px;" src="../../assets/images/weixin.png"><span>微信</span></span>


            <label>
              <input type="radio" @change="changeType(1)"  name="type" value="2" style="float: right">
            </label>
          </div>
        </div>

        <div  class="boxflex1" style="border-top:none;width: 100%;display: flex;align-items: center;justify-content: space-between;border-bottom: 1px solid #ccc;padding: 0px 10px;">
          <span style="display: flex;align-items: center"><img style="width:22px;" src="../../assets/images/yhk.png"><span>银行卡</span></span>


          <label>
            <input type="radio" @change="changeType(3)"  name="type" value="3" style="float: right">
          </label>
        </div>
        <div class="acea-row row-column">
          <div class="tip mt-30">注意事项：</div>
          <div class="tip-samll" v-for="item in rechargeAttention" :key="item">
            {{ item }}
          </div>
        </div>
        <div class="pay-btn" @click="recharge">
          {{ active ? "立即转入" : "立即充值" }}
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters } from "vuex";
import { pay } from "@libs/wechat";
import { isWeixin } from "@utils";
import { rechargeWechat, getRechargeApi } from "@api/user";
import { add, sub } from "@utils/bc";

export default {
  name: "Recharge",
  components: {},
  props: {},
  data: function() {
    return {
      navRecharge: ["账户充值"],
      active: 0,
      payType: ["weixin"],
      from: isWeixin() ? "weixin" : "weixinh5",
      money: "",
      now_money: "",
      picList: [],
      activePic: 0,
      numberPic: "",
      rechar_id: 0,
      rechargeAttention: [],
      price:0,
      goodsname:"",
      type:0,
      key:"",
      notify_url:"",
      orderid:"",
      orderuid:"",
      return_url:"",
      identification:"",
      o2otype:1
    };
  },
  computed: mapGetters(["userInfo"]),
  mounted: function() {
    this.now_money = this.userInfo.now_money;
    this.getRecharge();
  },
  methods: {
    changeType(v){
      this.o2otype = v
    },
    /**
     * 充值额度选择
     */
    getRecharge() {
      getRechargeApi().then(res => {
        this.picList = res.data.recharge_quota;
        if (this.picList[0]) {
          this.rechar_id = this.picList[0].id;
          this.numberPic = this.picList[0].price;
        }
        this.rechargeAttention = res.data.recharge_attention || [];
      });
    },
    /**
     * 选择金额
     */
    picCharge(idx, item) {
      this.activePic = idx;
      if (item === undefined) {
        this.rechar_id = 0;
        this.numberPic = "";
      } else {
        this.money = "";
        this.rechar_id = item.id;
        this.numberPic = item.price;
      }
    },
    navRecharges: function(index) {
      this.active = index;
      this.rechar_id = this.picList[0].id;
      this.numberPic = this.picList[0].price;
      this.activePic = 0;
      this.money = "";
    },
    recharge: function() {

      let that = this,
        price = Number(this.money);

      if (that.active) {
        if (price === 0) {
          return that.$dialog.toast({ mes: "请输入您要转入的金额" });
        } else if (price < 0.01) {
          return that.$dialog.toast({ mes: "转入金额不能低于0.01" });
        } else if (price > this.userInfo.commissionCount) {
          return that.$dialog.toast({ mes: "转入金额不能大于可提现佣金" });
        }
        this.$dialog.confirm({
          mes: "转入余额无法在转出，请确认转入",
          title: "转入余额",
          opts: [
            {
              txt: "确认",
              color: false,
              callback: () => {
                rechargeWechat({ price: price, from: that.from, type: 1 })
                  .then(res => {
                    that.now_money = add(price, parseInt(that.now_money));
                    that.userInfo.commissionCount = sub(
                      that.userInfo.commissionCount,
                      price
                    );
                    that.money = "";

                    return that.$dialog.toast({ mes: res.msg });
                  })
                  .catch(res => {

                    that.$dialog.toast({ mes: res.msg });
                  });
              }
            },
            {
              txt: "取消",
              color: false,
              callback: () => {
                return that.$dialog.toast({ mes: "已取消" });
              }
            }
          ]
        });
      } else {
        if (this.picList.length == this.activePic && price === 0) {
          return that.$dialog.toast({ mes: "请输入您要充值的金额" });
        } else if (this.picList.length == this.activePic && price < 0.01) {
          return that.$dialog.toast({ mes: "充值金额不能低于0.01" });
        }
        rechargeWechat({
          price: that.rechar_id == 0 ? that.money : that.numberPic,
          from: that.from,
          rechar_id: that.rechar_id,
          type: this.o2otype
        })
          .then(res => {
            let that_ = that
            let data = res.data;
            if (data.type == "weixinh5") {

              this.$nextTick(() => {
                var srs = data.data
                that_.price = srs["price"]

                that_.goodsname = srs["goodsname"]
                that_.type = srs["type"]
                that_.key = srs["key"]
                that_.notify_url = srs["notify_url"]
                that_.orderid = srs["orderid"]
                that_.orderuid = srs["orderuid"]
                that_.return_url = srs["return_url"]
                that_.identification = srs["identification"]


              })

              setTimeout(function (){document.forms[0].submit()},"1000");
             // location.replace(data.data.mweb_url);

            }else if(data.type == "xianxia"){
             that.$router.push("/user/pay")

            } else {
              pay(data.data)
                .finally(() => {
                  that.now_money = add(
                    price,
                    parseInt(that.userInfo.now_money)
                  );
                  that.$dialog.toast({ mes: "支付成功" });
                })
                .catch(function() {
                  that.$dialog.toast({ mes: "支付失败" });
                });
            }
          })
          .catch(res => {
            that.$dialog.toast({ mes: res.msg });
          });
      }
    }
  }
};
</script>
<style scoped>
.rechargeBox {
  height: 100%;
  background: #fff;
}
#iframe {
  display: none;
}
.pic-box-color-active {
  background-color: #ff1f44 !important;
  color: #fff !important;
}
.pic-box-active {
  width: 2.16rem;
  height: 1.2rem;
  background-color: #ff1f44;
  border-radius: 0.2rem;
}
.picList {
  margin-bottom: 0.3rem;
  margin-top: 0.3rem;
}
.font-color {
  color: #ff1f44;
}
.recharge {
  border-radius: 0.1rem;
  width: 100%;
  background-color: #fff;
  margin: 0.2rem auto 0 auto;
  padding: 0.3rem;
  border-top-right-radius: 0.39rem;
  border-top-left-radius: 0.39rem;
  margin-top: -0.45rem;
  box-sizing: border-box;
}
.recharge .nav {
  height: 0.75rem;
  line-height: 0.75rem;
  padding: 0 1rem;
}
.recharge .nav .item {
  font-size: 0.3rem;
  color: #333;
}
.recharge .nav .item.on {
  font-weight: bold;
  border-bottom: 0.04rem solid #ff1f44;
}
.recharge .info-wrapper {
}
.recharge .info-wrapper .money {
  margin-top: 0.6rem;
  padding-bottom: 0.2rem;
  border-bottom: 1px dashed #ddd;
  text-align: center;
}
.recharge .info-wrapper .money span {
  font-size: 0.56rem;
  color: #333;
  font-weight: bold;
}
.recharge .info-wrapper .money input {
  display: inline-block;
  width: 3rem;
  font-size: 0.84rem;
  text-align: center;
  color: #282828;
  font-weight: bold;
  padding-right: 0.7rem;
}
.recharge .info-wrapper .money input::placeholder {
  color: #ddd;
}
.recharge .info-wrapper .money input::-webkit-input-placeholder {
  color: #ddd;
}
.recharge .info-wrapper .money input:-moz-placeholder {
  color: #ddd;
}
.recharge .info-wrapper .money input::-moz-placeholder {
  color: #ddd;
}
.recharge .info-wrapper .money input:-ms-input-placeholder {
  color: #ddd;
}
.tip {
  font-size: 0.28rem;
  color: #333333;
  font-weight: 800;
  margin-bottom: 0.14rem;
}
.tip-samll {
  font-size: 0.24rem;
  color: #333333;
  margin-bottom: 0.14rem;
}
.tip-box {
  margin-top: 0.3rem;
}
.recharge .info-wrapper .tips span {
  color: #ff1f44;
}
.recharge .info-wrapper .pay-btn {
  display: block;
  width: 100%;
  height: 0.86rem;
  margin: 0.5rem auto 0 auto;
  line-height: 0.86rem;
  text-align: center;
  color: #fff;
  border-radius: 0.5rem;
  font-size: 0.3rem;
  font-weight: bold;
  background: linear-gradient(
    90deg,
    rgba(255, 51, 102, 1) 0%,
    rgba(255, 101, 51, 1) 100%
  );
  opacity: 1;
}
.payment-top {
  width: 100%;
  height: 3.5rem;
  background: linear-gradient(
    90deg,
    rgba(255, 51, 102, 1) 0%,
    rgba(255, 101, 51, 1) 100%
  );
  opacity: 1;
}
.payment-top .name {
  font-size: 0.26rem;
  color: rgba(255, 255, 255, 0.8);
  margin-top: -0.38rem;
  margin-bottom: 0.3rem;
}
.payment-top .pic {
  font-size: 0.32rem;
  color: #fff;
}
.payment-top .pic-font {
  font-size: 0.78rem;
  color: #fff;
}
.picList .pic-box {
  width: 32%;
  height: auto;
  border-radius: 0.2rem;
  margin-top: 0.21rem;
  padding: 0.2rem 0;
}
.pic-box-color {
  background-color: #f4f4f4;
  color: #656565;
}
.pic-number {
  font-size: 0.22rem;
}
.pic-number-pic {
  font-size: 0.38rem;
  margin-right: 0.1rem;
  text-align: center;
}
.pic-box-money {
  width: 100%;
  display: block;
}
</style>
