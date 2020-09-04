<template>
  <div style="background: #ed7a95;" class="quality-recommend">
    <div ><img onclick="javascript:history.go(-1)" src="http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/Icon_left_arrow.png" style="width: .5rem;height:.5rem; margin-left:0.3rem;margin-top:0.2rem;margin-bottom: 0.2rem"></div>

    <div class="banner">
      <img src="http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/xingp.jpg" alt="">
    </div>


    <div class="commodity">
      <div v-for="(item, index) in goodsList"
           :key="index">
        <router-link :to="{path:'/detail/'+item.id}"  style="display: flex;">
          <div class="goodsPic">
            <img :src=item.image alt="">
          </div>
          <div class="baseInfo">
            <div class="sss">{{item.store_name}}</div>
            <div style="color: #c70d00; margin:0.1rem 0">￥
              <span class="spjg">{{item.price}}</span>
            </div>
            <div class="tuanbut">
              <div class="tuan" style="width:50%;display: flex; align-items: center;">
                <img src="http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/tuan.png" style="width:0.3rem; height:0.3rem;      vertical-align:middle;">
                <span style="color: #6c6c6c; padding-left: 0.08rem;">{{item.tuan_number}}人团</span>
              </div>
              <div class="spqg" style="display: flex;width:50%; align-items: center;">
                <img src="http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/gou.png" style="width:0.3rem; height:0.3rem;      vertical-align:middle;">
                <span style="margin-left:0.05rem; padding-left: 0.08rem;">去开团</span>
              </div>
            </div>
          </div>
        </router-link>
      </div>

    </div>

  </div>
</template>
<script>
import { swiper, swiperSlide } from "vue-awesome-swiper";
import "@assets/css/swiper.min.css";
import GoodList from "@components/GoodList";
import { getGroomList } from "@api/store";
export default {
  name: "HotNewGoods",
  components: {
    swiper,
    swiperSlide,
    GoodList
  },
  props: {},
  data: function() {
    return {
      imgUrls: [],
      goodsList: [],
      name: "",
      icon: "",
      RecommendSwiper: {
        pagination: {
          el: ".swiper-pagination",
          clickable: true
        },
        autoplay: {
          disableOnInteraction: false,
          delay: 2000
        },
        loop: true,
        speed: 1000,
        observer: true,
        observeParents: true
      }
    };
  },
  mounted: function() {
    this.titleInfo();
    this.getIndexGroomList();
  },
  methods: {
    titleInfo: function() {
      let type = this.$route.params.type;
      if (type === "1") {
        this.name = "精品推荐";
        this.icon = "icon-jingpintuijian";
        document.title = "精品推荐";
      } else if (type === "2") {
        this.name = "热门榜单";
        this.icon = "icon-remen";
        document.title = "热门榜单";
      } else if (type === "3") {
        this.name = "首发新品";
        this.icon = "icon-xinpin";
        document.title = "首发新品";
      }
    },
    getIndexGroomList: function() {
      let that = this;
      let type = this.$route.params.type;
      console.log(type);
      getGroomList(type)
        .then(res => {
          that.imgUrls = res.data.banner;
          that.goodsList = res.data.list;
          console.log(res.data.list);
        })
        .catch(function(res) {
          this.$dialog.toast({ mes: res.msg });
        });
    }
  }
};
</script>
<style>

  .banner {
    width: 95vw;
    margin: 0 auto;
  }
  .banner img {
    width: 100%;
  }
  .commodity {
    background: #fff;
    margin: 0 0.1rem;
    padding: 0.3rem;
    font-size: 0.3rem;
    border-radius: 5px;
  }
  .commodity > div {
    margin-bottom: 0.3rem;
  }
  a, a:link, a:visited, a:hover, a:active {
    text-decoration: none;
    color: #000;
  }
  .goodsPic {
    width: 2rem;
    height: 2rem;
    float: left;
  }
  .goodsPic img {
    width: 100%;
    height: 100%;
  }
  .baseInfo {
    width: 66%;
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
  .tuanbut {
    display: flex;
    border: 1px solid #ef81d8;
    margin-right: 0.8rem;
    width: 100%;
  }
  .tuan {
    color: red;
    font-size: 0.28rem;
    font-weight: normal;
    padding: 0.09rem 0.2rem;
    border: 1px solid #ef81d8;
    border-radius: 5px 0 0 5px;
  }
  .spqg {
    background: linear-gradient(to right, #fc64d5, #ff8736);
    color: white;
    padding: 0.09rem 0.2rem;
    text-align: center;
    font-size: 0.28rem;
    font-weight: normal;
  }
</style>