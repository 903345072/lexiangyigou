const app = getApp();
import wxh from '../../utils/wxh.js';
import { getIndexData, getCoupons, getTemlIds, getLiveList } from '../../api/api.js';
import { CACHE_SUBSCRIBE_MESSAGE } from '../../config.js';
import { getCategoryList, getProductHot, getProductslist } from '../../api/store.js';
import { getCouponReceive} from '../../api/user.js';
import { getSeckillIndexTime, getSeckillList } from '../../api/activity.js';

import Util from '../../utils/util.js';

Page({
  /**
   * 页面的初始数据
   */
  data: {
    logoUrl:'',
    categoryOne:[],
    banner: [],
    menus: [],
    getCouponList:[],
    activity:[],
    timeList: [],
    killIndex: 0,//点击当前index值；
    seckillTimeIndex: 0, //当前秒杀index；
    killIndexTime: 0,//点击当前index值所对应的秒杀时间；
    killIndexLen: 0,//当前秒杀的产品列表长度
    seckillList:[],
    scrollLeft: 0,
    elementWidth:0,
    status:1,
    lovely: [],
    info: {
      fastList: [],
      bastBanner: [],
      firstList: [],
      bastList: []
    },
    avtiveIndex: 0,
    likeInfo: [],
    benefit:[],
    hostProduct: [],
    indicatorDots: false,
    circular: true,
    autoplay: true,
    intervalNew: 3500,
    durationNew: 700,
    parameter:{
      'navbar':'0',
      'return':'0',
      'class':'5'
    },
    window: false,
    iShiddenTip: false,
    isAuto: false, //是否自动授权；
    iShidden: true, //是否隐藏；
    isGoIndex: false, //是否返回首页；
    navH: "",
    recommend:{
      loadend: false,
      loading: false,
      loadTitle: '加载更多',
      page: 1,
      limit:20,
    },
    where: {
      page: 1,
      limit: 20,
      cid: 0, //一级分类id
      sid: 0 //二级分类id
    },
    loadend: false,
    loading: false,
    loadTitle: '加载更多',
    productList: [],
    categoryActive: 0,
    tapActive:0,
    countDownHour: "00",
    countDownMinute: "00",
    countDownSecond: "00",
    seckillCont:true,
    interval:null,
    newGoodsBananr: '',
    liveList: [],
    liveInfo: {}
  },
  closeTip:function(){
    wx.setStorageSync('msg_key',true);
    this.setData({
      iShiddenTip:true
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wxh.selfLocation(1);
    this.getCategoryData();
    this.getCoupon();
    this.get_host_product();
    this.get_product_list();
    this.setData({
      navH: app.globalData.navHeight
    });
    if (options.spid) app.globalData.spid = options.spid;
    if (options.scene) app.globalData.code = decodeURIComponent(options.scene);
    if (wx.getStorageSync('msg_key')) this.setData({ iShiddenTip:true});
    this.getTemlIds();
    this.getLiveList();
  },
  getTemlIds() {
    let messageTmplIds = wx.getStorageSync(CACHE_SUBSCRIBE_MESSAGE);
    if (!messageTmplIds) {
      getTemlIds().then(res => {
        if (res.data)
          wx.setStorageSync(CACHE_SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
      }).catch(()=>{})
    }
  },
  //授权
  onLoadFun: function () {
    this.getCoupon();
  },
  catchTouchMove: function (res) {
    return false
  },
  onColse:function(){
    this.setData({ window: false});
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },
  bindchange(e) {
    var index = e.detail.current;
    this.setData({ avtiveIndex: index});
  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getCoupon();
    this.getIndexConfig();
    this.getSeckillTime();
  },
  getIndexConfig:function(){
    var that = this;
    getIndexData().then(res=>{
      that.setData({
        banner: res.data.banner,
        menus: res.data.menus,
        activity: res.data.activity,
        lovely: res.data.lovely,
        info: res.data.info,
        likeInfo: res.data.likeInfo,
        benefit: res.data.benefit,
        logoUrl: res.data.logoUrl,
        couponList: res.data.couponList,
      });
      // 检测是否授权；scope.userInfo存在为授权；
      wx.getSetting({
        success(res) {
          if (!res.authSetting['scope.userInfo']) {
            that.setData({ window: that.data.couponList.length ? true : false });
          } else {
            that.setData({ window: false, iShidden: true});
          }
        }
      });
    })
  },
  getLiveList: function () {
    getLiveList(1, 20).then(res => {
      if (res.data.length == 1) {
        this.setData({ liveInfo: res.data[0] });
      } else {
        this.setData({ liveList: res.data });
      }
    }).catch(res => {

    })
  },
  /**
 * 商品详情跳转
 */
  goDetailType: function (e) {
    let item = e.currentTarget.dataset.items
    if (item.activity && item.activity.type === "1") {
      wx.navigateTo({
        url: `/pages/activity/goods_seckill_details/index?id=${item.activity.id}&time=${item.activity.time}&status=1`
      });
    } else if (item.activity && item.activity.type === "2") {
      wx.navigateTo({ url: `/pages/activity/goods_bargain_details/index?id=${item.activity.id}` });
    } else if (item.activity && item.activity.type === "3") {
      wx.navigateTo({
        url: `/pages/activity/goods_combination_details/index?id=${item.activity.id}`
      });
    } else {
      wx.navigateTo({ url: `/pages/goods_details/index?id=${item.id}` });
    }
  },
  /**
* 获取我的推荐
*/
  get_host_product: function (isPage) {
    var that = this;
    if (that.data.recommend.loadend) return;
    if (that.data.recommend.loading) return;
    if (isPage === true) that.setData({ hostProduct: [] });
    that.setData({'recommend.loading': true, 'recommend.loadTitle': ''});
    getProductHot(that.data.recommend.page, that.data.recommend.limit).then(res => {
      let list = res.data;
      let hostProduct = app.SplitArray(list, that.data.hostProduct);
      let loadend = list.length < that.data.recommend.limit;
      that.setData({
        'recommend.loadend': loadend,
        'recommend.loading': false,
        'recommend.loadTitle': loadend ? '已全部加载' : '加载更多',
        hostProduct: hostProduct,
        ['recommend.page']: that.data.recommend.page + 1,
      });
    }).catch(err => {
      that.setData({ loading: false, loadTitle: '加载更多' });
    });
  },
  categoryTap: function (event) {
    let that = this, tapActive = event.detail.index;
    that.setData({
      tapActive: tapActive
    })
    if (tapActive > 0) {
      that.setData({
         'where.page':1,
         loadend:false,
         loading:false,
         'where.cid': that.data.categoryOne[tapActive - 1].id,
         'where.sid': that.data.categoryOne[tapActive - 1].children.length
          ? that.data.categoryOne[tapActive - 1].children[0].id
          : -1,
        categoryActive:0
      })
      that.get_product_list(true);
    }
  },
  productTap: function (e) {
    let that = this,index = e.currentTarget.dataset.indexn;
    that.setData({
      categoryActive: index,
      'where.page':1,
      loadend:false,
      loading:false,
      'where.sid': that.data.categoryOne[that.data.tapActive - 1].children[index].id
    })
    that.get_product_list(true);
  },
  get_product_list: function (isPage){
    var that = this;
    if (that.data.loading) return;
    if (that.data.loadend) return;
    if (isPage === true) that.setData({ productList: [] });
    that.setData({ loading: true, loadTitle: '' });
    getProductslist(that.data.where).then(res => {
      let list = res.data;
      let productList = app.SplitArray(list, that.data.productList);
      let loadend = list.length < that.data.where.limit;
      that.setData({
        loadend: loadend,
        loading: false,
        loadTitle: loadend ? '已全部加载' : '加载更多',
        productList: productList,
        ['where.page']: that.data.where.page + 1
      });
    }).catch(err => {
      that.setData({ loading: false, loadTitle: '加载更多' });
    });
  },
  getSeckillTime: function () {
    let that = this;
    getSeckillIndexTime().then(res => {
      let timeList = res.data.seckillTime, seckillTimeIndex = res.data.seckillTimeIndex;
      that.setData({
        timeList: timeList,
        seckillCont: res.data.seckillCont,
        killIndex: seckillTimeIndex,
        seckillTimeIndex: seckillTimeIndex,
        killIndexTime: timeList[that.data.killIndex].stop,
        status: timeList[seckillTimeIndex].status
      })
      wxh.time(timeList[that.data.killIndex].stop, that,false);
      that.getSeckillLists(); 
    }).catch(()=>{
      
    });
  },
  setTime: function (e) {
    var that = this, index = e.currentTarget.dataset.index;
    that.setData({
      killIndex: index,
      status: that.data.timeList[index].status
    })
    that.getSeckillLists();
  },
  getSeckillLists: function () {
    let that = this;
    let timeId = that.data.timeList[that.data.killIndex].id;
    getSeckillList(timeId, { page: 1, limit: 20 }).then(res => {
      that.setData({
        seckillList: res.data
      })
      
      if (this.data.timeList.length){
        let query = wx.createSelectorQuery().in(this);
        query.select('.timeLen').boundingClientRect(function (res) {
          if(res){
            that.setData({
              scrollLeft: (that.data.killIndex - 1.8) * res.width
            });
          }
        }).exec();
        if (that.data.killIndex === that.data.seckillTimeIndex) {
          that.setData({
            killIndexLen: res.data.length
          })
        }
      }
    }).catch(()=>{});
  },
  goDetail: function (e) {
    let index = this.data.timeList[this.data.killIndex];
    wx.navigateTo({
      url: '/pages/activity/goods_seckill_details/index?id=' + e.currentTarget.dataset.id + '&time=' + index.stop + '&status=' + index.status
    })
  },
  getCategoryData: function () {
    let that = this;
    getCategoryList().then(res => {
      that.setData({
        categoryOne: res.data
      })
    });
  },
  getCoupon: function () {
    var that = this;
    getCoupons({page: 1,limit: 6}).then(res => {
      that.setData({
        getCouponList: res.data
      })
    }).catch(err => {
      // app.Tips({
      //   title: err
      // });
    });
  },
  receiveCoupon: function (e) {
    if (!app.globalData.isLog) {
      this.setData({
        iShidden: false
      });
    } else {
      var that = this;
      var list = that.data.getCouponList;
      var index = e.currentTarget.dataset.index;
      var id = that.data.getCouponList[index].id;
      getCouponReceive({
        couponId: id
      })
        .then(function () {
          list[index].is_use = true;
          that.setData({
            getCouponList: that.data.getCouponList
          })
          app.Tips({
            title: "领取成功"
          });
        })
        .catch(function (err) {
          // app.Tips({
          //   title: err
          // });
        });
    }
  },
  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
    this.setData({ window:false});
    this.data.interval !== null && clearInterval(this.data.interval);
  },
  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
    this.data.interval !== null && clearInterval(this.data.interval);
  },
  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
    this.getIndexConfig();
    wx.stopPullDownRefresh();
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    this.get_host_product();
    this.get_product_list();
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})