<template>
  <div class="productSort">
    <form @submit.prevent="submitForm">
      <div class="header acea-row row-center-wrapper" ref="header">
        <div class="acea-row row-between-wrapper input">
          <span class="iconfont icon-sousuo"></span>
          <input type="text" placeholder="搜索商品信息" v-model="search" />
        </div>
      </div>
    </form>
    <div style="background-color: white;padding:0.2rem 0.1rem;position: fixed;width: 100%;left:0;top:0.96rem;display: flex;justify-content: space-around;align-items: center">

      <div v-for="(item, index) in tuan_way" style="display: flex;flex-direction: column;justify-content: center;align-items: center;" @click="tuanAsideTap(index,item.number)" >
        <img style="width:1rem" :src=item.img>
        <span style="padding-top: 0.1rem" :class="index === tuan_native ? 'tuan_on' : ''" >{{item.name}}</span>
      </div>


    </div>
    <div class="aside">
      <div
        class="item acea-row row-center-wrapper"
        :class="index === navActive ? 'on' : ''"
        v-for="(item, index) in category"
        :key="index"
        @click="asideTap(index)"
      >
        <span>{{ item.cate_name }}</span>
      </div>
    </div>
    <div class="conter" >
      <div class="right">
        <div class="content1">
          <div v-for="(item, index) in product_list" :key="index">
            <router-link :to="{
              path: '/detail/'+item.id,

           }" style="display: flex; margin-bottom:0.3rem;" >
              <div class="goodsPic">
                <img :src=item.image alt="">
              </div>
              <div class="baseInfo">
                <div class="sss">{{item.store_name}}</div>
                <div style="color: #c70d00; margin: 0.05rem 0;">￥
                  <span class="spjg1" style="font-size: 0.25rem">{{item.price}}</span>
                </div>
                <div style="color: #c70d00;font-size: 0.24rem;margin-bottom: 0.11rem">拼团即返{{item.back_rate}}.00%</div>
                <div></div>
                <div class="tuanbut">
                  <div class="tuan" style="width:89vw;display: flex; align-items: center;">
                    <img src="http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/tuan.png" style="width:0.3rem; height:0.3rem;      vertical-align:middle;">
                    <span style="color: #6c6c6c; padding-left: 0.1rem;">{{item.tuan_number}}人团</span>

                  </div>
                  <div class="spqg" style="display: flex;width:26vw; align-items: center;">
                    <img src="http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/gou.png" style="width:0.3rem; height:0.3rem;      vertical-align:middle;">
                    <!-- <span style="margin-left:0.05rem;font-size:0.2rem">去开团</span> -->
                  </div>
                </div>
              </div>
              <div></div>
            </router-link>
          </div>
        </div>

      </div>
    </div>
    <div style="height:1.2rem;"></div>
  </div>
</template>
<style>
  .goodsPic {
    width: 1.8rem;
    height: 1.8rem;
  }
  .goodsPic img {
     width: 1.8rem;
     height: 1.8rem;
   }
  .sss {
    font-size: 0.25rem;
    font-family: SimHei;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    letter-spacing: 0.03rem;
  }
  .baseInfo {
    width: 64%;
    float: left;
    padding-left: 0.15rem;
  }
  .right{
    padding-top:0.1rem;
  }
  .tuanbut {
    display: flex;
    border: 0 solid #ef81d8;
    border-radius: 5px;
    width: 35vw;
    margin-right: 0.8rem;
  }
  .tuan {
    color: red;
    font-size: 0.25rem;
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
  .tuan_on{
    color:#fc64d5;
  }
</style>
<script>
import debounce from "lodash.debounce";
import { getCategory } from "@api/store";
import { trim } from "../../utils";

export default {
  name: "GoodsClass",
  components: {},
  props: {},
  data: function() {
    return {
      category: [],
      product_list: [],
      tuan_way: [{"img":"http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/10ren.png","name":"十人团","number":10},{"img":"http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/5ren.png","name":"五人团","number":5},{"img":"http://meida.vwbxyhj.cn//addons/sjlm_tg/template/mobile/images/2ren.png","name":"二人团","number":2}],
      navActive: 0,
      search: "",
      lock: false,
      tuan_native: null
    };
  },
  watch: {
    "$route.params.pid": function(n) {
      console.log(n);
      if (n) {
        this.activeCateId(n);
      }
    }
  },
  mounted: function() {
    document.addEventListener("scroll", this.onScroll, false);
    this.loadCategoryData();
  },
  methods: {
    activeCateId(n) {
      let index = 0;
      n = parseInt(n);
      if (n<0) return;
      this.category.forEach((cate, i) => {
        if (i === n) index = i;
      });

      if (index !== this.navActive) {
        this.asideTap(index);
      }
    },
    loadCategoryData() {

      getCategory().then(res => {
        this.category = res.data;
        this.product_list = res.data[0]["product"]

        this.$nextTick(() => {
          if (this.$route.params.pid) this.activeCateId(this.$route.params.pid);
        });
      });
    },
    submitForm: function() {
      var val = trim(this.search);
      if (val) {
        this.$router.push({
          path: "/goods_list",
          query: { s: val }
        });
        setTimeout(() => (this.search = ""), 500);
      }
    },
    asideTap(index) {
      var cur_product_list = []
      this.tuan_native = null;
      this.product_list = this.category[index]["product"]
      if(this.tuan_native >=0 && this.tuan_native != null){
        this.product_list.forEach((item, i) => {
          if(this.tuan_way[this.tuan_native] != null){
            if(item.tuan_number === this.tuan_way[this.tuan_native]["number"]){
              cur_product_list.push(item)
            }
          }

        });
        this.product_list = cur_product_list
      }else {
        this.product_list = this.category[index]["product"]
      }

              this.lock = true;
      this.navActive = index;
    },
    tuanAsideTap(index,number) {
      var cur_product_list = []
      //获取当前分类下指定团人数商品
      var s = this.category[this.navActive]["product"]
      s.forEach((item, i) => {
        if(item.tuan_number === number){
          cur_product_list.push(item)
        }
      });
      this.product_list = cur_product_list
      this.tuan_native = index;
    },
    // onScroll: debounce(function() {
    //   if (this.lock) {
    //     this.lock = false;
    //     return;
    //   }
    //   const headerHeight = this.$refs.header.offsetHeight,
    //     { scrollY } = window,
    //     titles = this.$refs.title;
    //   titles.reduce((initial, title, index) => {
    //     if (initial) return initial;
    //     const parent = title.parentNode || title.parentElement;
    //     if (
    //       scrollY + headerHeight + 15 <
    //       title.offsetTop + parent.offsetHeight
    //     ) {
    //       initial = true;
    //       this.navActive = index;
    //     }
    //     // else if (innerHeight + scrollY + 15 > offsetHeight) {
    //     //   this.navActive = titles.length - 1;
    //     //   initial = true;
    //     // }
    //     return initial;
    //   }, false);
    //   this.lock = false;
    // }, 500)
  },
  beforeDestroy: function() {
    document.removeEventListener("scroll", this.onScroll, false);
  }
};
</script>
