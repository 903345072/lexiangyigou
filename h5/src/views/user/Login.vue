<template>
  <div class="register absolute">
    <div class="shading">
      <div class="pictrue acea-row row-center-wrapper">
        <img :src="logoUrl" v-if="logoUrl" />
        <img src="@assets/images/logo2.png" v-else />
      </div>
    </div>
    <div class="whiteBg" v-if="formItem === 1">
      <div class="title acea-row row-center-wrapper">
        <div
          class="item"
          :class="current === index ? 'on' : ''"
          v-for="(item, index) in navList"
          @click="navTap(index)"
          :key="index"
        >
          {{ item }}
        </div>
      </div>
      <div class="list" :hidden="current !== 0">
        <form @submit.prevent="submit">
          <div class="item">
            <div class="acea-row row-between-wrapper">
              <svg class="icon" aria-hidden="true">
                <use xlink:href="#icon-phone_"></use>
              </svg>
              <input
                type="text"
                placeholder="输入手机号码"
                v-model="account"
                required
              />
            </div>
          </div>
          <div class="item">
            <div class="acea-row row-between-wrapper">
              <svg class="icon" aria-hidden="true">
                <use xlink:href="#icon-code_"></use>
              </svg>
              <input
                type="password"
                placeholder="填写登录密码"
                v-model="password"
                required
              />
            </div>
          </div>
        </form>
        <div
          class="forgetPwd"
          @click="$router.push({ name: 'RetrievePassword' })"
        >
          <span class="iconfont icon-wenti"></span>忘记密码
        </div>
      </div>
      <div class="list" :hidden="current !== 1">
        <div class="item">
          <div class="acea-row row-between-wrapper">
            <svg class="icon" aria-hidden="true">
              <use xlink:href="#icon-phone_"></use>
            </svg>
            <input type="text" placeholder="输入手机号码" v-model="account" />
          </div>
        </div>
        <div class="item">
          <div class="align-left">
            <svg class="icon" aria-hidden="true">
              <use xlink:href="#icon-code_1"></use>
            </svg>
            <input
              type="text"
              placeholder="填写验证码"
              class="codeIput"
              v-model="captcha"
            />
            <button
              class="code"
              :disabled="disabled"
              :class="disabled === true ? 'on' : ''"
              @click="code"
            >
              {{ text }}
            </button>
          </div>
        </div>
        <div class="item" v-if="isShowCode">
          <div class="align-left">
            <svg class="icon" aria-hidden="true">
              <use xlink:href="#icon-code_"></use>
            </svg>
            <input
              type="text"
              placeholder="填写验证码"
              class="codeIput"
              v-model="codeVal"
            />
            <div class="code" @click="again"><img :src="codeUrl" /></div>
          </div>
        </div>
      </div>
      <div class="logon" @click="loginMobile" :hidden="current !== 1">登录</div>
      <div class="logon" @click="submit" :hidden="current === 1">登录</div>
      <div class="tip">
        没有账号?
        <span @click="formItem = 2" class="font-color-red">立即注册</span>
      </div>
    </div>
    <div class="whiteBg" v-else>
      <div class="title">注册账号</div>
      <div class="list">
        <div class="item">
          <div>
            <svg class="icon" aria-hidden="true">
              <use xlink:href="#icon-phone_"></use>
            </svg>
            <input type="text" placeholder="输入手机号码" v-model="account" />
          </div>
        </div>
        <div class="item">
          <div>
            <svg class="icon" aria-hidden="true">
              <use xlink:href="#icon-code_1"></use>
            </svg>
            <input type="text" placeholder="输入姓名" v-model="name" />
          </div>
        </div>
        <div class="item">
          <div>
            <svg class="icon" aria-hidden="true">
              <use xlink:href="#icon-code_1"></use>
            </svg>
            <input type="text" placeholder="输入邀请码" v-model="invite_code" />
          </div>
        </div>
        <div class="item">
          <div class="align-left">
            <svg class="icon" aria-hidden="true">
              <use xlink:href="#icon-code_1"></use>
            </svg>
            <input
              type="text"
              placeholder="填写验证码"
              class="codeIput"
              v-model="captcha"
            />
            <button
              class="code"
              :disabled="disabled"
              :class="disabled === true ? 'on' : ''"
              @click="code"
            >
              {{ text }}
            </button>
          </div>
        </div>
        <div class="item">
          <div>
            <svg class="icon" aria-hidden="true">
              <use xlink:href="#icon-code_"></use>
            </svg>
            <input
              type="password"
              placeholder="填写您的登录密码"
              v-model="password"
            />
          </div>
        </div>
        <div class="item" v-if="isShowCode">
          <div class="align-left">
            <svg class="icon" aria-hidden="true">
              <use xlink:href="#icon-code_"></use>
            </svg>
            <input
              type="text"
              placeholder="填写验证码"
              class="codeIput"
              v-model="codeVal"
            />
            <div class="code" @click="again"><img :src="codeUrl" /></div>
          </div>
        </div>
      </div>
      <div style="margin-top: 0.1rem">
        <label>
          <input type="checkbox" style="" v-bind:checked="is_agree" v-on:click="handleDisabled" />
          我仔细阅读并同意网站<router-link style="color:blue" :to="{path:'/agreement'}">用户注册协议</router-link>
        </label>
      </div>
      <div class="logon" @click="register">注册</div>
      <div class="tip">
        已有账号?
        <span @click="formItem = 1" class="font-color-red">立即登录</span>
      </div>
    </div>
    <div class="bottom"></div>
  </div>
</template>
<script>
import sendVerifyCode from "@mixins/SendVerifyCode";
import {
  login,
  loginMobile,
  registerVerify,
  register,
  getCodeApi
} from "@api/user";
import attrs, { required, alpha_num, chs_phone,is_agreed } from "@utils/validate";
import { validatorDefaultCatch } from "@utils/dialog";
import { getLogo } from "@api/public";
import cookie from "@utils/store/cookie";
import { VUE_APP_API_URL } from "@utils";
const BACK_URL = "login_back_url";

export default {
  name: "Login",
  mixins: [sendVerifyCode],
  data: function() {
    return {
      navList: ["账号登录"],
      current: 0,
      account: "",
      password: "",
      captcha: "",
      formItem: 1,
      type: "login",
      name: "",
      logoUrl: "",
      keyCode: "",
      codeUrl: "",
      codeVal: "",
      invite_code: "",
      isShowCode: false,
      is_agree:false
    };
  },
  mounted: function() {
    this.getCode();
    this.getLogoImage();
    this.invite_code = this.getQueryVariable('code')?this.getQueryVariable('code'):'';

    if (this.getQueryVariable('code')){
      this.formItem = 0;
    }
    if (this.getQueryVariable('item')){
      this.formItem = 0;
    }
  },
  methods: {

    handleDisabled:function(){
      this.is_agree = !this.is_agree;
      if(this.is_agree===true){
        this.is_agree =  true;
      }
      else{
        this.is_agree =  false;
      }
      console.log(this.is_agree)
    },
   getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
      var pair = vars[i].split("=");
      if(pair[0] === variable){return pair[1];}
    }
    return false;
  },
    again() {
      this.codeUrl =
        VUE_APP_API_URL +
        "/sms_captcha?" +
        "key=" +
        this.keyCode +
        Date.parse(new Date());
    },
    getCode() {
      getCodeApi()
        .then(res => {
          this.keyCode = res.data.key;
        })
        .catch(res => {
          this.$dialog.error(res.msg);
        });
    },
    async getLogoImage() {
      let that = this;
      getLogo(2).then(res => {
        that.logoUrl = res.data.logo_url;
      });
    },
    async loginMobile() {
      var that = this;
      const { account, captcha } = that;
      try {
        await that
          .$validator({
            account: [
              required(required.message("手机号码")),
              chs_phone(chs_phone.message())
            ],
            captcha: [
              required(required.message("验证码")),
              alpha_num(alpha_num.message("验证码"))
            ]
          })
          .validate({ account, captcha });
      } catch (e) {
        return validatorDefaultCatch(e);
      }
      loginMobile({
        phone: that.account,
        captcha: that.captcha,
        spread: cookie.get("spread")
      })
        .then(res => {
          let data = res.data;
          let expires_time = data.expires_time.substring(0, 19);
          expires_time = expires_time.replace(/-/g, "/");
          expires_time = new Date(expires_time).getTime() - 28800000;
          // const expires_time = new Date(data.expires_time).getTime() / 1000;
          const datas = {
            token: data.token,
            expires_time: expires_time
          };

          this.$store.commit("LOGIN", datas);
          const backUrl = cookie.get(BACK_URL) || "/";
          cookie.remove(BACK_URL);
          that.$router.replace({ path: backUrl });
        })
        .catch(res => {
          that.$dialog.error(res.msg);
        });
    },
    async register() {
      var that = this;
      const { account, captcha, password,invite_code} = that;
      if (!this.is_agree){
        that.$dialog.error("请同意用户注册协议")
        return;
      }
      try {
        await that
          .$validator({

            account: [
              required(required.message("手机号码")),
              chs_phone(chs_phone.message())
            ],
            captcha: [
              required(required.message("验证码")),
              alpha_num(alpha_num.message("验证码"))
            ],
            password: [
              required(required.message("密码")),
              attrs.range([6, 16], attrs.range.message("密码")),
              alpha_num(alpha_num.message("密码"))
            ]
          })
          .validate({ account, captcha, password});
      } catch (e) {
        return validatorDefaultCatch(e);
      }
      register({
        invite_code: that.invite_code,
        account: that.account,
        captcha: that.captcha,
        password: that.password,
        spread: cookie.get("spread"),
        name: this.name
      })
        .then(res => {
          that.$dialog.success(res.msg);
          that.formItem = 1;
        })
        .catch(res => {
          that.$dialog.error(res.msg);
        });
    },
    async code() {
      var that = this;
      const { account } = that;
      try {
        await that
          .$validator({
            account: [
              required(required.message("手机号码")),
              chs_phone(chs_phone.message())
            ]
          })
          .validate({ account });
      } catch (e) {
        return validatorDefaultCatch(e);
      }
      if (that.formItem == 2) that.type = "register";
      await registerVerify({
        phone: that.account,
        type: "register",
        key: that.keyCode,
        code: that.codeVal
      })
        .then(res => {
          that.$dialog.success(res.msg);
          that.sendCode();
        })
        .catch(res => {
          if (res.data.status === 402) {
            that.codeUrl = `${VUE_APP_API_URL}/sms_captcha?key=${that.keyCode}`;
            that.isShowCode = true;
          }
          that.$dialog.error(res.msg);
        });
    },
    navTap: function(index) {
      this.current = index;
    },
    async submit() {
      const { account, password, codeVal, invite_code } = this;
      try {
        await this.$validator({
          account: [
            required(required.message("账号")),
            attrs.range([5, 16], attrs.range.message("账号")),
            alpha_num(alpha_num.message("账号"))
          ],
          password: [
            required(required.message("密码")),
            attrs.range([6, 16], attrs.range.message("密码")),
            alpha_num(alpha_num.message("密码"))
          ],
          codeVal: this.isShowCode
            ? [
                required(required.message("验证码")),
                attrs.length(4, attrs.length.message("验证码")),
                alpha_num(alpha_num.message("验证码"))
              ]
            : []
        }).validate({ account, password, codeVal });
      } catch (e) {
        return validatorDefaultCatch(e);
      }

      login({ account, password, code: codeVal })
        .then(({ data }) => {
          let expires_time = data.expires_time.substring(0, 19);
          expires_time = expires_time.replace(/-/g, "/");
          expires_time = new Date(expires_time).getTime() - 28800000;
          const datas = {
            token: data.token,
            expires_time: expires_time
          };
          localStorage.setItem("LOGIN",JSON.stringify(datas));
          this.$store.commit("LOGIN", datas);
          const backUrl = cookie.get(BACK_URL) || "/";
          cookie.remove(BACK_URL);
          this.$router.replace({ path: backUrl });
          //  let newTime = Math.round(new Date() / 1000);
          // this.$store.commit("LOGIN", data.token, dayjs(data.expires_time))213123
        })
        .catch(e => {
          this.$dialog.error(e.msg);
        });
    }
  }
};
</script>
<style scoped>
.code img {
  width: 100%;
  height: 100%;
}
</style>
