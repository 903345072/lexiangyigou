<template>
  <div class="user">
    <div class="header">
      <div class="figure">
        <div class="top acea-row row-between-wrapper">
          <div class="picTxt acea-row row-between-wrapper">
            <div class="pictrue"><img :src="userInfo.avatar" /></div>
            <div class="text">
              <div class="acea-row row-middle">
                <div class="name line1">{{ userInfo.nickname }}</div>
                <div class="member acea-row row-middle" v-if="userInfo.vip">
                  <img :src="userInfo.vip_icon" />{{ userInfo.vip_name }}
                </div>
              </div>
              <router-link :to="'/user/data'" class="id" v-if="userInfo.phone">
                ID：{{ userInfo.uid || 0
                }}<span class="iconfont icon-bianji1"></span>
              </router-link>
              <router-link
                :to="'/user/binding'"
                class="binding acea-row row-center-wrapper"
                v-else
              >
                <span>绑定手机号</span>
              </router-link>
            </div>
          </div>
          <span
            class="iconfont icon-shezhi"
            @click="$router.push({ path: '/user/data' })"
          ></span>
        </div>
        <div class="bottom acea-row row-between-wrapper">
          <div class="list acea-row row-around row-middle">
            <router-link :to="{ path: '/user/account' }" class="item">
              <div class="num">{{ userInfo.now_money || 0 }}</div>
              <div class="name">余额(元)</div>
            </router-link>
            <router-link :to="'/user/integral'" class="item">
              <div class="num">{{ userInfo.integral || 0 }}</div>
              <div class="name">积分</div>
            </router-link>
            <router-link :to="'/user/user_coupon'" class="item">
              <div class="num">{{ userInfo.couponCount || 0 }}</div>
              <div class="name">优惠券</div>
            </router-link>
            <router-link :to="'/order/list'" class="item">
              <div class="num">{{ userInfo.pay_count || 0 }}</div>
              <div class="name">消费订单</div>
            </router-link>
            <div
              :to="'/user/user_promotion'"
              class="extend"
              @click="goUrl('/user/user_promotion')"
            >
              <div class="iconfont icon-tuiguang"></div>
              <div>我的推广<span class="iconfont icon-xiangyou"></span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="activity acea-row row-between-wrapper">
      <router-link :to="'/user/vip'" class="item acea-row row-middle">
        <div class="pictrue"><img src="@assets/images/vipicon.png" /></div>
        <div class="text">
          <div class="name">会员中心</div>
          <div class="info">点击查看您的会员权益</div>
        </div>
      </router-link>
      <router-link :to="'/user/sign'" class="item acea-row row-middle">
        <div class="pictrue"><img src="@assets/images/signicon.png" /></div>
        <div class="text">
          <div class="name">每日一签</div>
          <div class="info">连续签到获得更多收益</div>
        </div>
      </router-link>
    </div>
    <router-link :to="'/user/get_coupon'" class="advert"
      ><img src="@assets/images/advert.png"
    /></router-link>
    <div class="wrapper">
      <div class="myOrder">
        <div class="title acea-row row-between-wrapper">
          <div class="name">订单1中心</div>
          <router-link :to="'/order/list/'" class="allOrder">
            查看全部<span class="iconfont icon-jiantou"></span>
          </router-link>
        </div>
        <div class="orderState acea-row row-middle">
          <router-link :to="{ path: '/order/list/' + 0 }" class="item">
            <div class="pictrue">
              <img src="@assets/images/dfk.png" />
              <span
                class="order-status-num"
                v-if="orderStatusNum.unpaid_count > 0"
                >{{ orderStatusNum.unpaid_count }}</span
              >
            </div>
            <div>待付款</div>
          </router-link>
          <router-link :to="{ path: '/order/list/' + 1 }" class="item">
            <div class="pictrue">
              <img src="@assets/images/dfh.png" />
              <span
                class="order-status-num"
                v-if="orderStatusNum.unshipped_count > 0"
                >{{ orderStatusNum.unshipped_count }}</span
              >
            </div>
            <div>待发货</div>
          </router-link>
          <router-link :to="{ path: '/order/list/' + 2 }" class="item">
            <div class="pictrue">
              <img src="@assets/images/dsh.png" />
              <span
                class="order-status-num"
                v-if="orderStatusNum.received_count > 0"
                >{{ orderStatusNum.received_count }}</span
              >
            </div>
            <div>待收货</div>
          </router-link>
          <router-link :to="{ path: '/order/list/' + 3 }" class="item">
            <div class="pictrue">
              <img src="@assets/images/dpj.png" />
              <span
                class="order-status-num"
                v-if="orderStatusNum.evaluated_count > 0"
                >{{ orderStatusNum.evaluated_count }}</span
              >
            </div>
            <div>待评价</div>
          </router-link>
          <router-link :to="'/order/refund_list'" class="item">
            <div class="pictrue">
              <img src="@assets/images/sh.png" />
              <span
                class="order-status-num"
                v-if="orderStatusNum.refund_count > 0"
                >{{ orderStatusNum.refund_count }}</span
              >
            </div>
            <div>售后/退款</div>
          </router-link>
        </div>
      </div>
      <div class="myService">
        <div class="title acea-row row-middle">我的服务</div>
        <div class="serviceList acea-row row-middle">
          <template v-for="(item, index) in MyMenus">
            <div
              class="item"
              :key="index"
              @click="goPages(index)"
              v-if="item.wap_url"
            >
              <div class="pictrue">
                <img :src="item.pic" />
              </div>
              <div>{{ item.name }}</div>
            </div>
          </template>
          <!--<div-->
          <!--class="item"-->
          <!--@click="changeswitch(true)"-->
          <!--v-if="userInfo.phone && isWeixin"-->
          <!--&gt;-->
          <!--<div class="pictrue"><img src="@assets/images/switch.png" /></div>-->
          <!--<div>账号切换</div>-->
          <!--</div>-->
        </div>
      </div>
    </div>
    <img src="@assets/images/support.png" class="support" />
    <div class="footer-line-height"></div>
    <SwitchWindow
      v-on:changeswitch="changeswitch"
      :switchActive="switchActive"
      :login_type="userInfo.login_type"
    ></SwitchWindow>
    <GeneralWindow
      :generalActive="generalActive"
      @closeGeneralWindow="closeGeneralWindow"
      :generalContent="generalContent"
    ></GeneralWindow>
  </div>
</template>
<script>
import { getUser, getMenuUser } from "@api/user";
import { isWeixin } from "@utils";
import SwitchWindow from "@components/SwitchWindow";
import GeneralWindow from "@components/GeneralWindow";
import cookie from "@utils/store/cookie";
const NAME = "User";

export default {
  name: NAME,
  components: {
    SwitchWindow,
    GeneralWindow
  },
  props: {},
  data: function() {
    return {
      userInfo: {},
      MyMenus: [],
      orderStatusNum: {},
      switchActive: false,
      isWeixin: false,
      generalActive: false,
      generalContent: {
        promoterNum: "",
        title: ""
      }
    };
  },
  watch: {
    $route(n) {
      if (n.name === NAME) this.User();
    }
  },
  mounted: function() {
    this.User();
    this.MenuUser();
    this.isWeixin = isWeixin();
    console.log("时间：", cookie.get("expires"));
  },
  methods: {
    changeswitch: function(data) {
      this.switchActive = data;
    },
    User: function() {
      let that = this;
      getUser().then(res => {
        that.userInfo = res.data;
        that.orderStatusNum = res.data.orderStatusNum;
        this.generalContent = {
          promoterNum: `您在商城累计消费金额仅差 <span style="color: #ff3366;">${res
            .data.promoter_price || 0}元</span>即可开通推广权限`,
          title: "您未获得推广权限"
        };
      });
    },
    MenuUser: function() {
      let that = this;
      getMenuUser().then(res => {
        that.MyMenus = res.data.routine_my_menus;
      });
    },
    goUrl: function(url) {
      if (url === "/user/user_promotion") {
        if (!this.userInfo.is_promoter && this.userInfo.statu == 1)
          return this.$dialog.toast({ mes: "您还没有推广权限！！" });
        if (!this.userInfo.is_promoter && this.userInfo.statu == 2) {
          return (this.generalActive = true);
        }
      }

      this.$router.push({ path: url });
    },
    goPages: function(index) {
      let url = this.MyMenus[index].wap_url;
      if (url === "/user/user_promotion") {
        if (!this.userInfo.is_promoter && this.userInfo.statu == 1)
          return this.$dialog.toast({ mes: "您还没有推广权限！！" });
        if (!this.userInfo.is_promoter && this.userInfo.statu == 2) {
          return (this.generalActive = true);
        }
      }
      if (url === "/customer/index" && !this.userInfo.adminid) {
        return this.$dialog.toast({ mes: "您还不是客服！！" });
      }

      this.$router.push({ path: this.MyMenus[index].wap_url });
    },
    closeGeneralWindow(msg) {
      this.generalActive = msg;
    }
  }
};
</script>

<style scoped>
.footer-line-height {
  height: 1rem;
}
.order-status-num {
  min-width: 0.33rem;
  background-color: #fff;
  color: #ee5a52;
  border-radius: 15px;
  position: absolute;
  right: -0.14rem;
  top: -0.15rem;
  font-size: 0.2rem;
  padding: 0 0.08rem;
  border: 1px solid #ee5a52;
}

.pictrue {
  position: relative;
}
.switch-h5 {
  margin-left: 0.2rem;
}
.binding {
  width: 1.6rem;
  height: 0.4rem;
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 0.2rem;
  font-size: 0.2rem;
  border: 0.01rem solid rgba(255, 255, 255, 0.5);
  color: #ffffff;
  margin-top: 0.15rem;
}
</style>
