<template>
  <div class="user">
    <header>

      <!-- <a href="#" class="top" id="clear">

              <i class="iconfont icon-msnui-conf-bold"></i>

          </a> -->

      <!--   <div class="top" id="clear" >onclick="goout()"

              用户注销

          </div> -->

      <div class="bottom">

        <div class="box">

          <div>

            <div class="name">

              <div class="headimg">





                <img :src=userInfo.avatar alt="">



              </div>

              <div class="userinfo">

                <!--  <span>冉</span><br /> -->

                <div><span>
                                ID:{{userInfo.uid}}
                                                                                                                                </span> </div>

                <div><span>余额：{{ userInfo.now_money || 0 }}</span> </div>

                <div><span>积分：{{ userInfo.integral || 0 }}</span> </div>

              </div>

            </div>

          </div>

        </div>

      </div>


    </header>
    <div class="huopin">
      <ul>
        <li>
          <router-link :to="{path:'/user/Recharge'}">
            <img class="iconfont" style="width:30px;height:30px;vertical-align: middle;" src="http://meida.lxt7.cn//addons/sjlm_tg/template/mobile/images/Icon_recharge.png">
            充值
          </router-link>
        </li>
        <li>
          <router-link :to="{path:'/user/user_cash'}">
            <img class="iconfont" style="width:30px;height:30px;vertical-align: middle;" src="http://meida.lxt7.cn//addons/sjlm_tg/template/mobile/images/Icon_withdraw.png">
            提现
          </router-link>
        </li>
      </ul>
    </div>

    <div class="wrapper" style="margin-top: 1rem">
      <div class="my-option">

        <ul>
          <li>
            <router-link :to="{path:'/user/add_manage'}" >
              <img class="iconfont" src="http://meida.lxt7.cn//addons/sjlm_tg/template/mobile/images/huo.png" style="padding:0.03rem;">
              我的收货地址

              <img style="float: right;margin-top: 0.2rem" src="../../assets/images/rightArrow.png" alt="">

            </router-link>

          </li>
          <li>

            <router-link :to="{path:'/user/account'}">

              <!-- <i class="iconfont icon-yongjin1"></i> -->
              <img class="iconfont" src="http://meida.lxt7.cn//addons/sjlm_tg/template/mobile/images/Icon_walles.png">
              我的钱包

              <img style="float: right;margin-top: 0.2rem" src="../../assets/images/rightArrow.png" alt="">


            </router-link>

          </li>
          <li>

            <router-link :to="{path:'/intergralProduct/'}">
              <img class="iconfont" src="http://meida.lxt7.cn//addons/sjlm_tg/template/mobile/images/Icon_integral_exchange.png">
              积分兑换
              <img style="float: right;margin-top: 0.2rem" src="../../assets/images/rightArrow.png" alt="">
            </router-link>

          </li>

          <li>

            <router-link :to="{path:'/user/bill/0'}">
              <img class="iconfont" src="http://meida.lxt7.cn//addons/sjlm_tg/template/mobile/images/Icon_capital_record.png">
              资金记录
              <img style="float: right;margin-top: 0.2rem" src="../../assets/images/rightArrow.png" alt="">
            </router-link>
          </li>


          <li>

            <router-link :to="{path:'/change_password'}">
              <img class="iconfont" src="http://meida.lxt7.cn//addons/sjlm_tg/template/mobile/images/Icon_update_password.png">
              修改密码
              <img style="float: right;margin-top: 0.2rem" src="../../assets/images/rightArrow.png" alt="">

            </router-link>

          </li>
          
          <li>

            <router-link :to="{path:'/login'}" >
              <img class="iconfont" src="http://meida.lxt7.cn//addons/sjlm_tg/template/mobile/images/Icon_update_password.png">
              退出登录
              <img style="float: right;margin-top: 0.2rem" src="../../assets/images/rightArrow.png" alt="">

            </router-link>

          </li>
        </ul>

      </div>

    </div>

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
import { getUser, getMenuUser,getLogout } from "@api/user";
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
    cookie.get("expires");
  },
  methods: {
      logout:function(){
          getLogout();
          this.$router.push({ path: "/login" });
          
      },
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
  .my-option ul {
    width: 95%;
    margin: 0 auto;
    border-radius: 8px;
    overflow: hidden;
  }
  .my-option li {
    height: 47px;
    width: 100%;
    background: #fff;
    justify-content: space-between;
  }
  .my-option li a {
    border: 0;
    font-weight: normal;
  }
  .my-option li a {
    display: inline-block;
    height: 47px;
    line-height: 47px;
    font-size: 16px;
    width: 100%;
    color: #1b1b1b;
    border-bottom: 1px solid #f0f0f0;
    text-indent: 10px;
    font-weight: 320;
  }
  .my-option img {
    width: 1.5em;
    vertical-align: middle;
    margin-top: -2px;
  }
  .huopin {
    border-radius: 5px;
    width: 95%;
    left: 2.5%;
  }
  .huopin {
    border-radius: .1rem;
    background: #fff;
    height: 1.3rem;
    line-height: 1.3rem;
    width: 95%;
    border: 1px solid #eee;
    position: absolute;
    top: 3.4rem;
    left: 2%;
  }
  .huopin ul li:nth-of-type(1) {
    border-right: 1px solid #eee;
  }
  .huopin ul li {
    line-height: 1.2rem;
    width: 50%;
    text-align: center;
    display: block;
    float: left;
    font-size: .3rem;
  }
  .user header {
    background: #f16287;
    color: #ffffff;
    position: relative;
    height: 4rem;
  }
  .user header .bottom {
    position: absolute;
    top: .8rem;
    width: 100%;
    color: #fff;
    padding: 0 10px;
  }
  .user header .bottom .box, header .name {
    display: -webkit-box;
    display: -moz-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    justify-content: space-between;
    -webkit-justify-content: space-between;
    -webkit-box-align: center;
    -moz-align-items: center;
    -webkit-align-items: center;
    align-items: center;
  }
  .headimg {
    margin-right: .1rem;
    margin-bottom: .2rem;
    position: relative;
    top: .2rem;
    border-radius: .2rem;
  }
  .headimg img{

    width: 4em;
    height: 4em;
    border-radius: 1rem;

  }

  .userinfo {
    margin-left: .3rem;
    margin-top: .3rem;
    font-size: 0.28rem;
  }

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
