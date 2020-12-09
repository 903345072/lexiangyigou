<template>
  <div v-cloak ref="container">
    <div class="indexs" v-if="status === 200">
      <div
        class="follow acea-row row-between-wrapper"
        v-if="followHid && isWeixin"
      >
        <div>点击“立即关注”即可关注公众号</div>
        <div class="acea-row row-middle">
          <div class="bnt" @click="followTap">立即关注</div>
          <span class="iconfont icon-guanbi" @click="closeFollow"></span>
        </div>
      </div>
      <div class="followCode" v-if="followCode">
        <div class="pictrue"><img :src="followUrl" /></div>
        <div class="mask" @click="closeFollowCode"></div>
      </div>

      <img  src="http://meida.lxt7.cn//addons/sjlm_tg/template/mobile/images/Icon_left1_arrow.png" onclick="javascript:history.go(-1)" style="width: .5rem;height:.5rem; margin-left:0.3rem;margin-top:0.2rem">
      <img src="../../assets/images/integral_banner.jpg" class="banner1">
      <div class="sign">
        <div class="jifen">我的积分 <span></span></div>
        <div class="signjf"><router-link :to="{path:'/user/sign'}">点击进入签到</router-link></div>
      </div>
        <div>
            <div  style="display: flex;justify-content: center">
                <LuckDraw
                        ref="luckDraw"
                        v-model="currIndex"
                        :awards="awards"
                        :async="true"
                        @before-start="handleBeforeStart"
                        @start="handleStart"
                        @end="handleEnd"
                        :radius=150

                        :borderColor="borderColor_"
                />
            </div>
        </div>
        <div style="display: flex;justify-content: center;margin-top:10px">
            <span style="color: red">*</span>每参与一次消耗150积分
        </div>
        <div class="wrapper">

<!--积分版块-->
          <div
                  class="recommend"
                  v-if="(info.bastList.length || info.bastBanner.length)"
          >


            <div class="hotList rexiaolist" style="padding: 4vw 0 4vw 3vw" v-if="integralInfo.length">
              <div class="list acea-row ">
                <router-link
                        :to="{ path: '/detail/' + item.id }"
                        class="item"
                        v-for="(item, index) in integralInfo"
                        :key="index"
                        style=" box-shadow: 5px 5px 5px 0 rgba(0, 0, 0, .08);
                              -webkit-box-shadow: #d4d2d2 0px 0px 10px;
                              -moz-box-shadow: #d4d2d2 0px 0px 10px;
                              border-radius: 10px;
                               width:43vw !important
"

                >
                  <div>

                      <div class="comlist">
                          <img :src=item.image alt="">
                          <div class="titless">{{item.store_name}}</div>
                          <div><span style="color: red;">{{item.intergral}}</span> 积分</div>
                      </div>


                  </div>
                </router-link>
              </div>
            </div>
          </div>
          <!--积分版块-->
        </div>

      <Coupon-window
        :coupon-list="couponList"
        v-if="showCoupon"
        @checked="couponClose"
        @close="couponClose"
      ></Coupon-window>
      <div style="height:1rem;"></div>
      <div>
        <iframe
          v-if="mapKey && !isWeixin"
          ref="geoPage"
          width="0"
          height="0"
          frameborder="0"
          scrolling="no"
          :src="
            'https://apis.map.qq.com/tools/geolocation?key=' +
              mapKey +
              '&referer=myapp'
          "
        >
        </iframe>
      </div>
    </div>
    <loadLogo v-else></loadLogo>
  </div>
</template>
<script>
import { swiper, swiperSlide } from "vue-awesome-swiper";
import "@assets/css/swiper.min.css";
import { Tab, Tabs } from "vant";
import CountDown from "@components/CountDown";
import CouponWindow from "@components/CouponWindow";
import Recommend from "@components/Recommend";
import cookie from "@utils/store/cookie";
import { openShareAll, wxShowLocation } from "@libs/wechat";
import { isWeixin } from "@utils/index";
import { getHomeData, getShare, follow } from "@api/public";
import { getHostProducts, getCategory, getProducts } from "@api/store";
import { getCoupon, getCouponReceive,getAward,draw } from "@api/user";
import { getSeckillConfig, getSeckillList } from "@api/activity";
import Loading from "@components/Loading";
import debounce from "lodash.debounce";
import { goShopDetail } from "@libs/order";
const HAS_COUPON_WINDOW = "has_coupon_window";
const LONGITUDE = "user_longitude";
const LATITUDE = "user_latitude";
let vm = null;
export default {
  name: "Index",
  components: {
    swiper,
    swiperSlide,
    Tab,
    Tabs,
    CountDown,
    CouponWindow,
    Loading,
    Recommend
  },
  props: {},
  data: function() {
    let that = this;
    return {
        is_playing:false,
        borderColor_:'#8a98ff',
        currIndex: 0, // 奖品的索引
        awards: [],
      r_is_sitile:true,
      j_is_sitile:false,
      isWeixin: isWeixin(),
      followUrl: "",
      subscribe: false,
      followHid: false,
      followCode: false,
      status: 0,
      logoUrl: "",
      banner: [],
      menus: [],
      activity: [],
      activityOne: [],
      lovely: [],
      info: {
        fastList: [],
        bastBanner: [],
        firstList: [],
        bastList: []
      },
      avtiveIndex: 0,
      likeInfo: [],
      integralInfo: [],
      benefit: [],
      hostProduct: [],
      page: 1,
      limit: 20,
      loading: false,
      loadend: false,
      getCouponList: [],
      timeList: [],
      killIndex: 0,
      seckillList: [],
      tapActive: 0,
      killIndexTime: 0,
      seckillTimeIndex: 0, //当前index；
      killIndexLen: 0,
      couponList: [],
      showCoupon: false,
      categoryOne: [],
      categoryActive: 0,
      where: {
        page: 1,
        limit: 20,
        cid: 0, //一级分类id
        sid: 0 //二级分类id
      },
      productList: [],
      loadings: false,
      loadends: false,
      seckillCont: true,
      swiperOption: {
        pagination: {
          el: ".paginationBanner",
          clickable: true
        },
        autoplay: {
          disableOnInteraction: false,
          delay: 2000
        },
        lazy: {
          loadPrevNext: true
        },
        // effect: "fade",
        loop: true,
        speed: 1000,
        observer: true,
        observeParents: true,
        on: {
          tap: function() {
            const realIndex = this.realIndex;
            vm.goUrl(realIndex);
          }
        }
      },
      swiperScroll: {
        freeMode: true,
        freeModeMomentum: false,
        slidesPerView: "auto",
        observer: true,
        observeParents: true
      },
      newProducts: {
        autoplay: {
          disableOnInteraction: false,
          delay: 2000
        },
        loop: true,
        effect: "coverflow",
        centeredSlides: true,
        slidesPerView: "auto",
        speed: 1000,
        touchRatio: 1,
        coverflowEffect: {
          rotate: 0, // 旋转的角度
          stretch: 20, // 拉伸   图片间左右的间距和密集度
          depth: 200, // 深度   切换图片间上下的间距和密集度
          modifier: 2, // 修正值 该值越大前面的效果越明显
          slideShadows: false // 页面阴影效果
        },
        on: {
          slideChangeTransitionStart: function() {
            that.avtiveIndex = this.realIndex;
          },
          tap: function() {
            const realIndex = this.realIndex;
            let item = that.info.firstList[realIndex];
            vm.goDetails(item);
          }
        },
        observer: true,
        observeParents: true
      },
      recommendOption: {
        pagination: {
          el: ".recommendBanner",
          clickable: true
        },
        autoplay: {
          disableOnInteraction: false,
          delay: 3000
        },
        loop: true,
        speed: 1000,
        observer: true,
        observeParents: true
      },
      classifyScroll: {
        freeMode: true,
        freeModeMomentum: false,
        slidesPerView: "auto",
        observer: true,
        observeParents: true
      },
      mapKey: ""
    };
  },
  created() {
    vm = this;
  },
  computed: {
    swiper() {
      return this.$refs.mySwiper.swiper;
    },
    newSwiper() {
      return this.$refs.newSwiper.swiper;
    },
    recommendSwiper() {
      return this.$refs.recommendSwiper.swiper;
    }
  },
  watch: {
    tapActive(to) {
      if (to === 0) {
        this.tapActive = 0;
        this.swiper.autoplay.start();
        this.newSwiper.autoplay.start();
        this.recommendSwiper.autoplay.start();
      } else {
        this.swiper.autoplay.stop();
        this.newSwiper.autoplay.stop();
        this.recommendSwiper.autoplay.stop();
      }
    },
    $route(n) {
      if (n.name === "Index") {
        this.getCoupon();
        this.swiper.autoplay.start();
        this.newSwiper.autoplay.start();
        this.recommendSwiper.autoplay.start();
      } else {
        this.swiper.autoplay.stop();
        this.newSwiper.autoplay.stop();
        this.recommendSwiper.autoplay.stop();
      }
    }
  },
  mounted: function() {
      this.getAward_();
    this.getFollow();
    this.getHomeData();
    this.getCoupon();
    this.getSeckillTime();
    this.hostProducts();
    this.getCategoryData();
    this.$scroll(this.$refs.container, () => {
      !this.loading && this.hostProducts();
    });
    cookie.get("expires");
  },
  methods: {

      getAward_(){
          let that = this
          getAward()
              .then(function(res) {
                  that.awards = res.data.awards
              })
              .catch(function(res) {

              });
      },

      handleStart () {

          console.log('开始抽奖')
      },
      handleEnd (index) {
          this.is_playing = false;
          var that = this
          if(this.awards[this.currIndex].img){
              that.$layer.confirm('<div style="display: flex;justify-content:center;align-items:center;flex-direction: column">\n' +
                  '    <div style="white-space: nowrap;font-size: 15px;margin-bottom: 10px">恭喜你抽中了<span style="color:red">'+this.awards[this.currIndex].name+'</span>,请截图联系客服兑换奖品</div>\n' +
                  '    <img style="width: 120px;height: 100px" src='+this.awards[this.currIndex].img+' alt="">\n' +
                  '</div>', {
                  btn: ['确定','取消']     //按钮
              }, function(){           //点击确定访问后台
                  that.$layer.closeAll();
              }, function(){   	//点击取消则中断操作
                  that.$layer.closeAll();
              })
          }else {
              that.$layer.confirm('谢谢惠顾', {
                  btn: ['确定','取消']     //按钮
              }, function(){           //点击确定访问后台
                  that.$layer.closeAll();
              }, function(){   	//点击取消则中断操作
                  that.$layer.closeAll();
              })
          }

          //alert('恭喜您抽到大奖, 奖品为' + this.awards[this.currIndex].name)
      },
      handleBeforeStart () { // 新增异步抽奖
          let that = this
          if (!that.is_playing){

              draw()
                  .then(function(res) {
                      that.is_playing = true
                      that.$refs.luckDraw.play(res.data.index)
                  })
                  .catch(function(res) {
                      that.$dialog.toast({ mes: res.data.data.msg });
                  });
          }


      },
    set_r_sititle(){
      this.r_is_sitile = true
      this.j_is_sitile = false
    },
    set_j_sititle(){
      this.j_is_sitile = true
      this.r_is_sitile = false
    },
    closeFollow() {
      this.followHid = false;
    },
    followTap() {
      this.followCode = true;
      this.followHid = false;
    },
    closeFollowCode() {
      this.followCode = false;
      this.followHid = true;
    },
    getFollow() {
      follow()
        .then(res => {
          this.followUrl = res.data.path;
        })
        .catch(() => {});
    },
    // 轮播图跳转
    goUrl(index) {
      let url = this.banner[index].wap_url;
      let newStr = url.indexOf("http") === 0;
      if (newStr) {
        window.location.href = url;
      } else {
        this.$router.push({
          path: url
        });
      }
    },
    // 商品详情跳转
    goDetails(item) {
      goShopDetail(item).then(() => {
        this.$router.push({ path: "/detail/" + item.id });
      });
    },
    getHomeData: function() {
      let that = this;
      getHomeData().then(res => {
        that.status = res.status;
        that.mapKey = res.data.tengxun_map_key;
        that.logoUrl = res.data.logoUrl;
        that.subscribe = res.data.subscribe;
        if (!that.subscribe && that.followUrl) {
          setTimeout(function() {
            that.followHid = true;
          }, 200);
        } else {
          that.followHid = false;
        }
        that.$set(that, "banner", res.data.banner);
        that.$set(that, "menus", res.data.menus);
        that.$set(that, "activity", res.data.activity);
        if (res.data.activity.length) {
          var activityOne = res.data.activity.splice(1, 1);
          that.$set(that, "activityOne", activityOne);
        }
        that.$set(that, "lovely", res.data.lovely);
        that.$set(that, "info", res.data.info);
        that.$set(that, "likeInfo", res.data.likeInfo);
        that.$set(that, "integralInfo", res.data.integralInfo);
        that.$set(that, "benefit", res.data.benefit);
        that.$set(that, "couponList", res.data.couponList);
        that.setOpenShare();
        this.showCoupon =
          !cookie.has(HAS_COUPON_WINDOW) &&
          res.data.couponList.some(coupon => coupon.is_use);
        if (!cookie.get(LATITUDE) && !cookie.get(LONGITUDE))
          this.getWXLocation();
      });
    },
    getWXLocation() {
      if (isWeixin()) {
        wxShowLocation();
      } else {
        if (!this.mapKey) console.log("无法使用地图缺少腾讯地图KEY");
        let loc;
        // if (_this.$route.params.mapKey) _this.locationShow = true;
        //监听定位组件的message事件
        window.addEventListener(
          "message",
          function(event) {
            loc = event.data; // 接收位置信息 LONGITUDE
            console.log("location", loc);
            if (loc && loc.module == "geolocation") {
              cookie.set(LATITUDE, loc.lat);
              cookie.set(LONGITUDE, loc.lng);
            } else {
              cookie.remove(LATITUDE);
              cookie.remove(LONGITUDE);
              //定位组件在定位失败后，也会触发message, event.data为null
              console.log("定位失败");
            }
          },
          false
        );
      }
    },
    hostProducts: function() {
      let that = this;
      if (that.loading) return; //阻止下次请求（false可以进行请求）；
      if (that.loadend) return; //阻止结束当前请求（false可以进行请求）；
      that.loading = true;
      getHostProducts(that.page, that.limit).then(res => {
        that.loading = false;
        //apply();js将一个数组插入另一个数组;
        that.hostProduct.push.apply(that.hostProduct, res.data);
        that.loadend = res.data.length < that.limit; //判断所有数据是否加载完成；
        that.page = that.page + 1;
      });
    },
    getCoupon: function() {
      let that = this;
      let q = { page: 1, limit: 6 };
      getCoupon(q).then(res => {
        that.$set(that, "getCouponList", res.data);
      });
    },
    receiveCoupon: function(id, index) {
      let that = this;
      let list = that.getCouponList;
      getCouponReceive(id)
        .then(function() {
          list[index].is_use = true;
          that.$dialog.toast({ mes: "领取成功" });
        })
        .catch(function(res) {
          that.$dialog.toast({ mes: res.msg });
        });
    },
    getSeckillTime: function() {
      let that = this;
      getSeckillConfig().then(res => {
        that.$set(that, "timeList", res.data.seckillTime);
        that.seckillCont = res.data.seckillCont;
        that.killIndex = res.data.seckillTimeIndex;
        that.seckillTimeIndex = res.data.seckillTimeIndex;
        that.killIndexTime = res.data.seckillTime[that.killIndex].stop;
        that.getSeckillList();
        that.$nextTick(function() {
          if (that.killIndexLen) that.$refs.timeList.scrollIntoView();
        });
      });
    },
    setTime: function(index) {
      var that = this;
      that.killIndex = index;
      that.getSeckillList();
    },
    getSeckillList: function() {
      let that = this;
      let timeId = that.timeList[that.killIndex].id;
      getSeckillList(timeId, { page: 1, limit: 20 }).then(res => {
        that.$set(that, "seckillList", res.data);
        if (that.killIndex === that.seckillTimeIndex) {
          that.killIndexLen = res.data.length;
        }
      });
    },
    goDetail: function(id, status) {
      var that = this;
      var time = that.timeList[that.killIndex].stop;
      this.$router.push({
        path: "/activity/seckill_detail/" + id + "/" + time + "/" + status
      });
    },
    getCategoryData: function() {
      let that = this;
      getCategory().then(res => {
        that.$set(that, "categoryOne", res.data);
      });
    },
    get_product_list: debounce(function() {
      let that = this;
      if (that.loadings) return; //阻止下次请求（false可以进行请求）；
      if (that.loadends) return; //阻止结束当前请求（false可以进行请求）；
      that.loadings = true;
      let q = that.where;
      getProducts(q).then(res => {
        that.loadings = false;
        that.productList.push.apply(that.productList, res.data);
        that.loadends = res.data.length < that.where.limit; //判断所有数据是否加载完成；
        that.where.page = that.where.page + 1;
      });
    }, 300),
    categoryTap: function() {
      let that = this;
      if (that.tapActive > 0) {
        that.$set(that, "productList", []);
        that.where.page = 1;
        that.loadends = false;
        that.loadings = false;
        that.where.cid = that.categoryOne[that.tapActive - 1].id;
        that.where.sid = that.categoryOne[that.tapActive - 1].children.length
          ? that.categoryOne[that.tapActive - 1].children[0].id
          : -1;
        that.categoryActive = 0;
        that.get_product_list();
        this.$scroll(this.$refs.container, () => {
          !this.loadings && this.get_product_list();
        });
      }
    },
    productTap: function(index) {
      let that = this;
      that.categoryActive = index;
      that.$set(that, "productList", []);
      that.where.page = 1;
      that.loadends = false;
      that.loadings = false;
      that.where.sid = that.categoryOne[that.tapActive - 1].children[index].id;
      that.get_product_list();
      this.$scroll(this.$refs.container, () => {
        !this.loadings && this.get_product_list();
      });
    },
    couponClose() {
      cookie.set(HAS_COUPON_WINDOW, 1);
    },
    setOpenShare: function() {
      if (isWeixin()) {
        getShare().then(res => {
          var data = res.data.data;
          var configAppMessage = {
            desc: data.synopsis,
            title: data.title,
            link: location.href,
            imgUrl: data.img
          };
          openShareAll(configAppMessage);
        });
      }
    }
  }
};
</script>

<style scoped>


[v-cloak] {
  display: none;
}
.index {
  background-color: #fff;
}
.aui-palace {
  margin: 0.2rem 0.3rem 0 0.3rem;
  position: relative;
  overflow: hidden;
}
a:visited {
  text-decoration: none !important;
}
.aui-palace-grid {
  position: relative;
  float: left;
  padding: 1px;
  width: 25%;
  box-sizing: border-box;
  margin: 5px 0;
}
.aui-palace-grid-icon {
  width: 55px;
  height: 55px;
  margin: 0 auto;
}
.aui-palace-grid-icon img {
  display: block;
  width: 100%;
  height: 100%;
  border: none;
}
.aui-palace-grid-text {
  display: block;
  text-align: center;
  color: #333;
  font-size: 0.85rem;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}
.aui-palace-grid-text h2 {
  font-size: 0.26rem;
  font-weight: normal;
  color: #333;
}
.index .follow {
  z-index: 100000;
}
.indexs .publicTitle {
  position: relative;
  width: 100%;
  margin: 0.3rem auto 0.5rem auto;
}
.indexs .publicTitle img {
  width: 4.31rem;
  height: 0.88rem;
  display: block;
}
.indexs .publicTitle .more {
  position: absolute;
  font-size: 0.24rem;
  color: #666;
  right: 0.2rem;
}
.indexs .header {
  height: 0.98rem;
  width: 100%;
  background: linear-gradient(to left, #ff3366 0%, #ff6533 100%);
  background: -webkit-linear-gradient(to left, #ff3366 0%, #ff6533 100%);
  background: -moz-linear-gradient(to left, #ff3366 0%, #ff6533 100%);
}
.indexs .header .logo {
  width: 1.27rem;
  height: 0.45rem;
  margin-right: 0.25rem;
}
.indexs .header .logo img {
  width: 100%;
  height: 100%;
  display: block;
}
.indexs .header .search {
  width: 5rem;
  height: 0.64rem;
  background-color: #f7f7f7;
  border-radius: 0.5rem;
  padding: 0 0.28rem;
  font-size: 0.28rem;
  color: #bbb;
}
.indexs .header .search .iconfont {
  font-size: 0.34rem;
  margin-right: 0.16rem;
}
.indexs .wrapper {
  width: 7.5rem;
}
.indexs .banner {
  height: 3rem;
}
.indexs .banner .pictrue {
  width: 100%;
  height: 100%;
}
.indexs .banner img {
  width: 100%;
  height: 100%;
}
.indexs .banner .swiper-pagination {
  bottom: 0.13rem;
}
.indexs .nav {
  padding-top: 0.26rem;
  width: 7.5rem;
}
.indexs .nav .item {
  width: 20%;
  text-align: center;
  font-size: 0.26rem;
  margin-bottom: 0.29rem;
  color: #282828;
}
.indexs .nav .item .pictrue {
  width: 0.9rem;
  height: 0.9rem;
  margin: 0 auto 0.08rem auto;
}
.indexs .nav .item .pictrue img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.indexs .scroll-coupon {
  white-space: nowrap;
  overflow: hidden;
  margin-top: 0.1rem;
}
.indexs .scroll-coupon .more {
  width: 0.36rem !important;
  height: 0.96rem;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 0.06rem;
  margin-right: 0.2rem;
  -webkit-writing-mode: vertical-rl;
  writing-mode: vertical-rl;
  font-size: 0.17rem;
  color: #666;
  padding: 0.09rem 0.01rem;
}
.indexs .scroll-coupon .swiper-slide {
  width: 3.1rem;
  margin-left: 0.2rem;
}
.indexs .scroll-coupon .item {
  display: inline-block;
  width: 100%;
  height: 0.96rem;
  background: url("../../assets/images/coupon01.png") no-repeat;
  background-size: 100% 100%;
  color: #fff;
  position: relative;
}
.indexs .scroll-coupon .item .lable {
  position: absolute;
  width: 0.62rem;
  height: 0.62rem;
  display: block;
  top: 0;
  right: 0;
}
.indexs .scroll-coupon .item.on {
  background-image: url("../../assets/images/coupon02.png");
}
.indexs .scroll-coupon .item .coupon-slide {
  padding: 0.05rem 0;
  height: 100%;
}
.indexs .scroll-coupon .item .coupon-slide .money {
  font-size: 0.3rem;
  color: #fff;
  margin-right: 0.1rem;
}
.indexs .scroll-coupon .item .coupon-slide .money .num {
  font-family: "GuildfordProBook 5";
  font-size: 0.48rem;
}
.indexs .scroll-coupon .item .coupon-slide .minPrice {
  font-size: 0.24rem;
  color: #feeaae;
}
.indexs .scroll-coupon .item .coupon-slide .minPrice .num {
  font-family: "GuildfordProBook 5";
}
.indexs .scroll-coupon .item.on .coupon-slide .minPrice {
  color: #fff;
}
.indexs .scroll-coupon .item .coupon-slide .time {
  font-size: 0.16rem;
  color: #fefefe;
}
.indexs .activity .title {
  width: 100%;
  height: 0.85rem;
  margin-top: 0.39rem;
}
.indexs .activity .title img {
  width: 100%;
  height: 100%;
}
.indexs .activity .activityCon {
  padding: 0 0.2rem;
  margin-top: 0.43rem;
}
.indexs .activity .activityCon .left {
  width: 2.46rem;
  height: 3.46rem;
  background-repeat: no-repeat;
  background-size: 100% 100%;
  padding: 0.35rem 0.18rem;
  box-shadow: 0 0 0.1rem 0.1rem #f8f8f8;
  -webkit-box-shadow: 0 0 0.1rem 0.1rem #f8f8f8;
  -moz-box-shadow: 0 0 0.1rem 0.1rem #f8f8f8;
  -o-box-shadow: 0 0 0.1rem 0.1rem #f8f8f8;
}
.indexs .activity .activityCon .left .name,
.indexs .activity .activityCon .right .item .name {
  font-size: 0.36rem;
  background: linear-gradient(to left, #ff3366 0%, #ff6533 100%);
  background: -webkit-linear-gradient(to left, #ff3366 0%, #ff6533 100%);
  background: -moz-linear-gradient(to left, #ff3366 0%, #ff6533 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: bold;
}
.indexs .activity .activityCon .left .info,
.indexs .activity .activityCon .right .item .info {
  font-size: 0.2rem;
  color: #2c2c2c;
  margin-bottom: 0.04rem;
}
.indexs .activity .activityCon .right {
  height: 3.46rem;
}
.indexs .activity .activityCon .right .item {
  background-repeat: no-repeat;
  width: 4.48rem;
  height: 1.64rem;
  background-size: 100% 100%;
  padding: 0.35rem 0.18rem;
  box-shadow: 0 0 0.1rem 0.1rem #f8f8f8;
  -webkit-box-shadow: 0 0 0.1rem 0.1rem #f8f8f8;
  -moz-box-shadow: 0 0 0.1rem 0.1rem #f8f8f8;
  -o-box-shadow: 0 0 0.1rem 0.1rem #f8f8f8;
}
.indexs .activity .activityCon .right .item:nth-of-type(1) .name {
  background: linear-gradient(to left, #000 0%, #555 100%);
  background: -webkit-linear-gradient(to left, #000 0%, #555 100%);
  background: -moz-linear-gradient(to left, #000 0%, #555 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: bold;
}
.indexs .flashSale .title {
  margin: 0.3rem 0;
  padding: 0 0.2rem;
}
.indexs .flashSale .title .left .pictrue {
  width: 1.36rem;
  height: 0.4rem;
  margin-right: 0.12rem;
}
.indexs .flashSale .title .left .pictrue img {
  width: 100%;
  height: 100%;
  display: block;
}
.indexs .flashSale .title .left .text {
  font-size: 0.16rem;
  color: #666;
}
.indexs .flashSale .title .left .text .chinese {
  font-size: 0.18rem;
  color: #999;
}
.indexs .flashSale .title .right {
  font-size: 0.24rem;
  color: #999;
}
.indexs .flashSale .title .right .item ~ .item {
  margin-left: 0.2rem;
}
.indexs .flashSale .title .right .item .iconfont {
  color: #ff2c58;
  font-size: 0.25rem;
  margin-right: 0.1rem;
}
.indexs .flashSale .van-tabs--line {
  position: unset;
}
.indexs .flashSale .pictrueBg {
  width: 7.1rem;
  height: 2.62rem;
  margin: 0 auto;
}
.indexs .flashSale .pictrueBg img {
  width: 100%;
  height: 100%;
  display: block;
}
.indexs .flashSale .tabCon {
  margin: -2.4rem auto 0 auto;
  width: 7.1rem;
  overflow: hidden;
}
.indexs .flashSale .van-tabs--line {
  padding-top: 0 !important;
}
.indexs .flashSale .tabCon .timeItem {
  color: #feeaae;
}
.indexs .flashSale .tabCon .timeItem .time {
  font-size: 0.38rem;
  font-family: "GuildfordProBook 5";
}
.indexs .flashSale .tabCon .timeItem .state {
  font-size: 0.22rem;
}
.indexs .flashSale .tabCon .list {
  margin: 0.3rem 0 0 0.3rem;
}
.indexs .flashSale .tabCon .list .swiper-slide {
  width: 2.5rem;
  margin-right: 0.2rem;
}
.indexs .flashSale .tabCon .list .item {
  width: 100%;
}
.indexs .flashSale .tabCon .list .item .grab {
  background-color: #999;
  color: #fff;
  font-size: 0.16rem;
  width: 0.9rem;
  height: 0.4rem;
  text-align: center;
  line-height: 0.4rem;
  border-radius: 0.08rem;
}
.indexs .flashSale .tabCon .list .item .pictrue {
  width: 100%;
  height: 2.5rem;
  border-radius: 0.06rem 0.06rem 0 0;
  position: relative;
}
.indexs .flashSale .tabCon .list .item .pictrue img {
  width: 100%;
  height: 100%;
  border-radius: 0.06rem 0.06rem 0 0;
  display: block;
}
.indexs .flashSale .tabCon .list .item .pictrue .bar {
  position: absolute;
  width: 2.3rem;
  height: 0.22rem;
  border-radius: 0.11rem;
  background-color: #ffefef;
  bottom: 0.1rem;
  left: 50%;
  margin-left: -1.15rem;
  font-size: 0.16rem;
  color: #fff;
}
.indexs .flashSale .tabCon .list .item .pictrue .bar .pos {
  width: 100%;
  height: 100%;
  position: relative;
}
.indexs .flashSale .tabCon .list .item .pictrue .bar .stock {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  margin-left: 0.2rem;
  color: #ffb9b9 !important;
}
.indexs .flashSale .tabCon .list .item .pictrue .bar .num {
  width: 1.2rem;
  height: 100%;
  background: linear-gradient(to left, #ff3366 0%, #ff6533 100%);
  background: -webkit-linear-gradient(to left, #ff3366 0%, #ff6533 100%);
  background: -moz-linear-gradient(to left, #ff3366 0%, #ff6533 100%);
  border-radius: 0.11rem;
}
.indexs .flashSale .tabCon .list .item .text {
  width: 100%;
  margin-top: 0.18rem;
}
.indexs .flashSale .tabCon .list .item .text .name {
  font-size: 0.28rem;
  color: #333;
  width: 100%;
}
.indexs .flashSale .tabCon .list .item .text .ot-money {
  font-size: 0.2rem;
  color: #aaa;
  text-decoration-line: line-through;
  margin-top: 0.02rem;
}
.indexs .flashSale .tabCon .list .item .text .money {
  font-size: 0.24rem;
}
.indexs .flashSale .tabCon .list .item .text .money .num {
  font-size: 0.3rem;
  font-weight: bold;
}
.indexs .advert {
  width: 100%;
  height: 1.8rem;
  margin-top: 0.33rem;
  display: block;
}
.indexs .advert img {
  width: 100%;
  height: 100%;
}
.indexs .newProducts .newSwiper {
  padding: 0 0.5rem;
}
.indexs .newProducts .newSwiper .swiper-slide {
  width: 4rem;
}
.indexs .newProducts .newSwiper .item {
  width: 100%;
  height: 4rem;
  display: block;
}
.indexs .newProducts .newSwiper .item img {
  width: 100%;
  height: 100%;
  display: block;
}
.indexs .newProducts .newSwiper .text {
  width: 4rem;
  margin: 0.2rem auto 0 auto;
}
.indexs .newProducts .newSwiper .text .name {
  font-size: 0.28rem;
  color: #282828;
  text-align: center;
}
.indexs .newProducts .newSwiper .text .money {
  font-size: 0.24rem;
  text-align: center;
}
.indexs .newProducts .newSwiper .text .money .num {
  font-size: 0.32rem;
  font-weight: bold;
}
.indexs .recommendSwiper {
  width: 7.1rem;
  margin: 0 auto 0.2rem auto;
  height: 2.66rem;
}
.indexs .recommendSwiper .pictrue {
  width: 100%;
  height: 100%;
}
.indexs .publicList {
  padding: 0 0.2rem;
}
.indexs .publicList .item {
  width: 3.45rem;
  margin-bottom: 0.37rem;
}
.indexs .publicList .item .pictrue {
  width: 100%;
  height: 3.45rem;
  position: relative;
}
.indexs .publicList .item .pictrue img {
  width: 100%;
  height: 100%;
  border-radius: 0.1rem;
}
.indexs .publicList .item .text {
  width: 100%;
}
.indexs .publicList .item .text .name {
  font-size: 0.3rem;
  color: #282828;
  width: 100%;
  margin: 0.2rem 0 0.05rem 0;
}
.indexs .publicList .item .text .money {
  font-size: 0.24rem;
  height: 0.55rem;
}
.indexs .publicList .item .text .money .label {
  font-size: 0.18rem;
  color: #fff;
  padding: 0 0.05rem;
  border-radius: 0.03rem;
  margin-left: 0.08rem;
}
.indexs .publicList .item .text .money .num {
  font-size: 0.36rem;
}
.indexs .publicList .item .text .ot-money {
  font-size: 0.22rem;
  color: #aaa;
  text-decoration: line-through;
  margin-top: -0.1rem;
}
.indexs .publicList .item .text .cart {
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 50%;
  background-color: #f5f5f5;
}
.indexs .publicList .item .text .cart .iconfont {
  color: #000;
  font-size: 0.25rem;
}
.indexs .hotList {
  width: 100%;
  overflow: hidden;
}
.indexs .hotList .list .item {
  width: 3.53rem;
  margin: 0 0 0.3rem 0.2rem;
}
.indexs .hotList .list .item .pictrue {
  width: 100%;
  height: 3.53rem;
  position: relative;
}
.indexs .hotList .list .item .pictrue img {
  width: 100%;
  height: 100%;
}
.indexs .hotList .list .item .pictrue .icon {
  width: 0.38rem;
  height: 0.43rem;
  display: block;
  position: absolute;
  top: 0.1rem;
  left: 0.15rem;
}
.indexs .hotList .list .item .text .name {
  width: 100%;
  font-size: 0.28rem;
  color: #282828;
  margin-top: 0.15rem;
}
.indexs .hotList .list .item .text .money {
  font-size: 0.24rem;
}
.indexs .hotList .list .item .text .money .num {
  font-size: 0.3rem;
}
.indexs .hotList .list .item .text .money .ot-money {
  font-size: 0.2rem;
  color: #aaa;
  text-decoration: line-through;
  margin-left: 0.1rem;
}
.indexs .recommend .title {
  width: 4.26rem;
  height: 0.79rem;
  margin: 0.4rem auto;
}
.indexs .recommend .title img {
  width: 100%;
  height: 100%;
}
/*分类*/
.indexs .scroll-classify .swiper-container {
  margin: 0;
}
.indexs .scroll-classify {
  white-space: nowrap;
  overflow: hidden;
  height: 1.8rem;
  width: 100%;
  box-shadow: 0 0.1rem 0.1rem #f9f9f9;
  -webkit-box-shadow: 0 0.1rem 0.1rem #f9f9f9;
  -o-box-shadow: 0 0.1rem 0.1rem #f9f9f9;
  -moz-box-shadow: 0 0.1rem 0.1rem #f9f9f9;
  border-radius: 0 0 0.3rem 0.3rem;
}
.indexs .scroll-classify .swiper-slide {
  width: 1.4rem;
}
.indexs .scroll-classify .item {
  width: 100%;
  margin-left: 0.2rem;
}
.indexs .scroll-classify .item .pictrue {
  width: 0.9rem;
  height: 0.9rem;
  border-radius: 50%;
  margin: 0 auto;
}
.indexs .scroll-classify .item .pictrue img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 0.02rem solid #fff;
}
.indexs .scroll-classify .item .name {
  font-size: 0.24rem;
  color: #282828;
  text-align: center;
  margin-top: 0.09rem;
}
.indexs .scroll-classify .item.on .pictrue img {
  border-color: #ff3366;
}
.indexs .scroll-classify .item.on .name {
  color: #ff3366;
}
.indexs .classifyList {
  padding: 0 0.2rem;
  margin-top: 0.2rem;
}
.indexs .classifyList .item {
  width: 3.45rem;
  margin-bottom: 0.46rem;
}
.indexs .classifyList .item .pictrue {
  width: 100%;
  height: 3.45rem;
  border-radius: 0.1rem 0.1rem 0 0;
  position: relative;
}
.indexs .classifyList .item .pictrue img {
  width: 100%;
  height: 100%;
  border-radius: 0.1rem 0.1rem 0 0;
}
.indexs .classifyList .item .text {
  width: 100%;
}
.indexs .classifyList .item .text .name {
  font-size: 0.3rem;
  color: #222;
  margin-top: 0.2rem;
}
.indexs .classifyList .item .text .money {
  font-size: 0.24rem;
  font-weight: bold;
  margin-top: 0.05rem;
}
.indexs .classifyList .item .text .money .num {
  font-size: 0.38rem;
  font-family: "GuildfordProBook 5";
}
.indexs .classifyList .item .text .vipMoney {
  font-size: 0.24rem;
  color: #282828;
  font-weight: bold;
}
.indexs .classifyList .item .text .vipMoney img {
  display: inline-block;
  width: 0.46rem;
  height: 0.21rem;
}
.indexs .classifyList .item .text .sales {
  font-size: 0.22rem;
  color: #aaa;
}
.head {
  width: 95%;
  font-size: .2rem;
  margin: 2px auto 15px auto;
  margin-top: 2px;
  height: 0.5rem;
  line-height: 0.5rem;
  border-radius: 10px;
  background: #FFE9E8;
  color: #FA6560;
  margin-top: 0.2rem;
}
.head > i {
  position: relative;
  top: .1rem;
  left: .2rem;
  display: inline-block;
  width: .51rem;
  height: .58rem;
  margin-top: 0.1rem;
  background-size: 100%;
}
.glyphicon {
  position: relative;
  top: 1px;
  display: inline-block;
  font-family: 'Glyphicons Halflings';
  font-style: normal;
  font-weight: normal;
  line-height: 1;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.flex-center {
  width: 85%;
  float: right;
  padding-right: .1rem;
}
#demo {
  width: auto;
  height: 0.6rem;
  overflow: hidden;
}
.head i {
  width: .3rem;
  top: .03rem;
}
.shouyindex {
  border-top: 8px solid #efefef;
  border-bottom: 8px solid #efefef;
  padding: 10px 0;
}
.xinrenzs {
  position: relative;
}
.xinrenzs > img {
  width: 100%;
}
.xinrenzs_con {
  position: absolute;
  top: 1rem;
  left: 0.4rem;
  display: inline-block;
}
.xinrenzs_con a {
  width: 43vw;
}
.xinrenzs_con img {
  width: 100%;
}
.xinrenzs_con a {
  width: 43vw;
}
.zuixingp {
  width: 100%;
  display: inline-block;
}
.zuixingp a {
  width: 45vw;
  margin-left: 3vw;
}
.zuixingp img {
  width: 100%;
  display: inline-block;
  box-shadow: 5px 5px 5px 0 rgba(0, 0, 0, .08);
  -webkit-box-shadow: #d4d2d2 0px 0px 10px;
  -moz-box-shadow: #d4d2d2 0px 0px 10px;
  border-radius: 10px;
}
.relistimg {
  width: 43vw !important;
  height: 43vw !important;
  border-radius: 10px 10px 0 0;
}
.sss {
  font-size: 0.25rem !important;
}
.rxcenter {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.rxjf {
  color: #c70d00;
}
.spjg {
  font-size: 0.38rem;
  color: #c70d00;
  font-family: Impact;
}
.spjg span {
  font-size: 0.21rem;
  margin-right: 4px;
}
.rxgwimg {
  width: 0.5rem !important;
  height: 0.5rem !important;
}
.splis_title {
  display: flex;
  justify-content: flex-start;
  padding: 0 3%;
}
.splis_title > div {
  padding: 0.1rem 0.4rem;
  font-size: 0.31rem;
  font-family: SimHei;
  font-weight: bold;
}
.stitle {
  background: linear-gradient(to right, #fc64d5, #ff8736);
  background-clip: border-box;
  -webkit-background-clip: text;
  color: transparent;
  border-bottom: 1px solid #fc64d5;
}
.splis_title > div {
  padding: 0.1rem 0.4rem;
  font-size: 0.31rem;
  font-family: SimHei;
  font-weight: bold;
}
.jifenimg {
  width: 43vw !important;
  height: 43vw !important;
  border-radius: 10px 10px 0 0;
}
.baseInfo {
  padding: 10px;
  width: 80%;
  float: left;
  padding-left: 0.15rem;
}
.sss {
  font-size: 0.3rem;
  font-family: SimHei;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  letter-spacing: 0.03rem;
}
.banner1 {
  width: 100%;
  border-bottom: 1px solid #e5e5e5;
}
.sign {
  display: flex;
  justify-content: space-between;
  padding: 0.1rem 0.5rem 0.3rem 0.5rem;
  border-bottom: 1px solid #efefef;
  margin-bottom: 0.3rem;
}
.sign > div {
  padding: 0 0.6rem;
}
.jifen {
  font-size: 0.3rem;
  text-align: center;
}
.jifen span {
  font-size: 0.4rem;
  color: #d1a158;
}
.sign > div {
  padding: 0 0.6rem;
}
.signjf {
  display: block;
  margin-top: 0.1rem;
}
.signjf {
  font-size: 0.3rem;
  border-left: 1px solid #d1a158;
}
a, a:link, a:visited, a:hover, a:active {
  text-decoration: none;
  color: #000;
}
.comlist {
    width: 40vw;
    padding: 0rem 0.2rem 0.2rem 0.2rem;
    text-align: center;
    float: left;
}
.comlist img {
    width: 100%;
    height: 40vw;
}
.comlist div {
    margin-top: 0.08rem;
}
.titless {
    font-size: 0.3rem;
    font-family: SimHei;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    letter-spacing: 0.03rem;

}

.bannera {
    display: block;
    width: 79%;
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 20px;
}
.bannera .turnplate {
    display: block;
    width: 100%;
    position: relative;
}
.turnplate {
    background: url("http://meida.lxt7.cn//addons/sjlm_tg/template/mobile/images/turnplate-bg.png");
    background-size: auto;
    background-size: 100% 100%;
}
.bannera .turnplate canvas.item {
    width: 100%;
}
.bannera .turnplate img.pointer {
    position: absolute;
    width: 31.5%;
    height: 42.5%;
    left: 34.1%;
    top: 23%;
}
</style>
