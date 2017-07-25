//1.判断是否是移动端
isMobile = {
    //判断设备是否是Android设备
    Android: function() {
        return navigator.userAgent.match(/Android/i) ? true : false;
    },
    //判断设备是否是BlackBerry设备
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i) ? true : false;
    },
    //判断设备是否是iOS设备
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i) ? true : false;
    },
    //判断设备是否是Windows设备
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i) ? true : false;
    },
    //判断当前浏览器是否是微信内置浏览器,是返回true,否则返回false
    WeChat : function () {
        return navigator.userAgent.toLowerCase().match(/MicroMessenger/i) == 'micromessenger' ? true : false;
    },
    //判断是否是移动端设备
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Windows());
    }
};
//2.操作工具函数
isTool = {
    //阻止浏览器默认事件
    preventDefault : function (e) {
        var e = e || window.event;
        e.preventDefault && e.preventDefault();
        e.returnValue = false;
    },
    //取消阻止浏览器默认事件
    stopPropagation : function (e) {
        var e = e || window.event;
        e.stopPropagation && e.stopPropagation();
        e.cancelBubble = false;
    },
    //禁止滚动
    disableScroll : function (jQueryObj) {
        var jQueryObj = jQueryObj || $(document);
        jQueryObj.on('mousewheel', this.preventDefault);
        jQueryObj.on('touchmove', this.preventDefault);
        return this;
    },
    //开启滚动
    enableScroll : function (jQueryObj) {
        var jQueryObj = jQueryObj || $(document);
        jQueryObj.off('mousewheel', this.preventDefault);
        jQueryObj.off('touchmove', this.preventDefault);
        return this;
    },
    //数字格式化
    numberformat : function (num) {
        var num = (num || 0).toString(),
            xiao = '';
        if(num.indexOf('.') <= -1) {
            xiao = '.00';
        }else {
            var nums = num.split('.');
            xiao = '.'.nums[1];
            num = nums[0];
        }
        var result = '';
        while (num.length > 3){
            result = ',' + num.slice(-3) + result;
            num = num.slice(0, num.length - 3);
        }
        if(num){result = num + result;}
        return result+xiao;
    }
};
//3.加载中
isLoading = {
    show : function () {
        if(!$('#core_loading').length) this.init();
        $('#core_loading').show();
    },
    hide : function () {
        $('#core_loading').hide();
    },
    init : function () {
        var _loading_html = '<div id="core_loading" style="top: 50%; left: 50%; margin-left: -35px; margin-top: -30px; position: fixed; width: 80px; z-index: 999999; display: none;"><img src="'+ ThinkPHP['IMG'] +'/loading.svg" width="80"></div>';
        $('body').append(_loading_html);
    }
};
//4.tip效果,需要3秒自动隐藏
isTip = {
    show : function (text) {
        this.init(text);
        $('#istips_show').show();
        this.hide();
    },
    hide : function () {
        setTimeout(function () {
            $('#istips_show').hide().html('');
        }, 2000);
    },
    init : function (text) {
        var _tip_html = '<div id="istips_show" style="width:60%;font-size:14px;min-width:150px;background:#000;color:#fff;opacity: 0.6;min-height:35px;line-height:35px;text-align:center;border-radius:5px;position:fixed;left:20%;bottom:60px;display: none;z-index: 9999;">'+ text +'</div>';
        if(!$('#istips_show').length) $('body').append(_tip_html);
        else $('#istips_show').text(text);
    }
};

//2.plupload图片上传插件封装，options配置参数对象，包括buttonid,multi,total属性
Plupload = {
    //plupload插件配置
    create : function (options) {
        var options = options || {},
            _buttonid = options.buttonid,//按钮id
            upload_total = options.total || 1,//最多上传数量,默认上传一张
            _multi = options.multi,//是否允许多选
            _thisplupload = this;
        var uploader = new plupload.Uploader({
            runtimes : 'html5,flash,silverlight,html4',
            browse_button : _buttonid,
            url : ThinkPHP['IMAGEURL'],
            flash_swf_url : ThinkPHP['PLUPLOAD']+'/js/Moxie.swf',
            silverlight_xap_url : ThinkPHP['PLUPLOAD']+'/js/Moxie.xap',
            filters : {
                max_file_size : '5mb',
                mime_types: [
                    {title : "图片文件", extensions : "jpg,gif,png"}
                ],
                prevent_duplicates : true //不允许选取重复文件
            },
            multi_selection: _multi, //true:ctrl多文件上传, false 单文件上传
            init: {
                PostInit : function () {
                    _thisplupload.imgRemove(_buttonid);
                },
                //文件上传前
                FilesAdded: function(up, files) {
                    var length_has_upload = $("#"+_buttonid).parent().children(".img").length - 1;
                    if (files.length >= upload_total) {//超过上传总数量则隐藏
                        $('#'+_buttonid).addClass('disabled');
                    }
                    var imghtml = '';
                    plupload.each(files, function(file) {//遍历文件
                        if (length_has_upload < upload_total) {
                            imghtml += '<div class="img" id="' + file['id'] + '"><div class="progress active"><div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"><span class="sr-only">0%</span></div></div></div>';
                        }
                        length_has_upload++;
                    });
                    if(imghtml != '') {
                        $("#"+_buttonid).parent().append(imghtml);
                        uploader.start();
                    }
                },
                //上传中，显示进度条
                UploadProgress: function(up, file) {
                    var percent = file.percent;
                    $("#" + file.id).find('.progress-bar').css({"width": percent + "%"});
                    $("#" + file.id).find(".sr-only").text(percent + "%");
                },
                //文件上传成功的时候触发
                FileUploaded: function(up, file, info) {
                    var uploaded_length = $("#"+_buttonid).parent().find('.img').length-1;
                    if (uploaded_length <= upload_total) {
                        var imageUrl = $.parseJSON(info.response);
                        $("#" + file.id).html('<img src=' + ThinkPHP["ROOT"] + imageUrl["source"].substr(1) + '><input type="hidden" name="images[]" value=' + imageUrl["source"] + '><div class="minus"><i class="ion-android-close remove-btn"></i></div>');
                    }
                    $('#'+_buttonid).removeClass('disabled');
                },
                //上传出错的时候触发
                Error: function(up, err) {
                    console.log(err.message);
                }
            }
        });
        uploader.init();
    },
    //图片删除
    imgRemove : function (_buttonid) {
        //删除图片
        $("#"+_buttonid).parent().on('click', '.remove-btn', function () {
            $(this).parents('.img').fadeOut(function () {
                $(this).remove();
            });
        });
    }
};

//相册预览功能，gallerySelector表示当前画册所在的容器，可以是class容器也可以睡
var initPhotoSwipeFromDOM = function(gallerySelector) {
    var parseThumbnailElements = function(el) {
        var thumbElements = el.childNodes,
            numNodes = thumbElements.length,
            items = [],
            el,
            childElements,
            thumbnailEl,
            size,
            item;
        for(var i = 0; i < numNodes; i++) {
            el = thumbElements[i];
            // include only element nodes
            if(el.nodeType !== 1) {
                continue;
            }
            childElements = el.children;
            size = el.getAttribute('data-size').split('x');
            // create slide object
            item = {
                src: el.getAttribute('href'),
                w: parseInt(size[0], 10),
                h: parseInt(size[1], 10),
                author: el.getAttribute('data-author')
            };
            item.el = el; // save link to element for getThumbBoundsFn
            if(childElements.length > 0) {
                item.msrc = childElements[0].getAttribute('src'); // thumbnail url
                if(childElements.length > 1) {
                    item.title = childElements[1].innerHTML; // caption (contents of figure)
                }
            }
            var mediumSrc = el.getAttribute('data-med');
            if(mediumSrc) {
                size = el.getAttribute('data-med-size').split('x');
                // "medium-sized" image
                item.m = {
                    src: mediumSrc,
                    w: parseInt(size[0], 10),
                    h: parseInt(size[1], 10)
                };
            }
            // original image
            item.o = {
                src: item.src,
                w: item.w,
                h: item.h
            };
            items.push(item);
        }
        return items;
    };
    // find nearest parent element
    var closest = function closest(el, fn) {
        return el && ( fn(el) ? el : closest(el.parentNode, fn) );
    };
    var onThumbnailsClick = function(e) {
        e = e || window.event;
        e.preventDefault ? e.preventDefault() : e.returnValue = false;
        var eTarget = e.target || e.srcElement;
        var clickedListItem = closest(eTarget, function(el) {
            return el.tagName === 'A';
        });
        if(!clickedListItem) {
            return;
        }
        var clickedGallery = clickedListItem.parentNode;
        var childNodes = clickedListItem.parentNode.childNodes,
            numChildNodes = childNodes.length,
            nodeIndex = 0,
            index;
        for (var i = 0; i < numChildNodes; i++) {
            if(childNodes[i].nodeType !== 1) {
                continue;
            }
            if(childNodes[i] === clickedListItem) {
                index = nodeIndex;
                break;
            }
            nodeIndex++;
        }
        if(index >= 0) {
            openPhotoSwipe( index, clickedGallery );
        }
        return false;
    };
    var photoswipeParseHash = function() {
        var hash = window.location.hash.substring(1),
            params = {};

        if(hash.length < 5) { // pid=1
            return params;
        }
        var vars = hash.split('&');
        for (var i = 0; i < vars.length; i++) {
            if(!vars[i]) {
                continue;
            }
            var pair = vars[i].split('=');
            if(pair.length < 2) {
                continue;
            }
            params[pair[0]] = pair[1];
        }

        if(params.gid) {
            params.gid = parseInt(params.gid, 10);
        }

        return params;
    };
    var openPhotoSwipe = function(index, galleryElement, disableAnimation, fromURL) {
        var pswpElement = document.querySelectorAll('.pswp')[0],
            gallery,
            options,
            items;
        items = parseThumbnailElements(galleryElement);
        // define options (if needed)
        options = {
            galleryUID: galleryElement.getAttribute('data-pswp-uid'),
            getThumbBoundsFn: function(index) {
                // See Options->getThumbBoundsFn section of docs for more info
                var thumbnail = items[index].el.children[0],
                    pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
                    rect = thumbnail.getBoundingClientRect();

                return {x:rect.left, y:rect.top + pageYScroll, w:rect.width};
            },
            addCaptionHTMLFn: function(item, captionEl, isFake) {
                if(!item.title) {
                    captionEl.children[0].innerText = '';
                    return false;
                }
                captionEl.children[0].innerHTML = item.title +  '<br/><small>Photo: ' + item.author + '</small>';
                return true;
            },

        };
        if(fromURL) {
            if(options.galleryPIDs) {
                // parse real index when custom PIDs are used
                // http://photoswipe.com/documentation/faq.html#custom-pid-in-url
                for(var j = 0; j < items.length; j++) {
                    if(items[j].pid == index) {
                        options.index = j;
                        break;
                    }
                }
            } else {
                options.index = parseInt(index, 10) - 1;
            }
        } else {
            options.index = parseInt(index, 10);
        }
        // exit if index not found
        if( isNaN(options.index) ) {
            return;
        }
        var radios = document.getElementsByName('gallery-style');
        for (var i = 0, length = radios.length; i < length; i++) {
            if (radios[i].checked) {
                if(radios[i].id == 'radio-all-controls') {
                    //...
                } else if(radios[i].id == 'radio-minimal-black') {
                    options.mainClass = 'pswp--minimal--dark';
                    options.barsSize = {top:0,bottom:0};
                    options.captionEl = false;
                    options.fullscreenEl = false;
                    options.shareEl = false;
                    options.bgOpacity = 0.9;
                    options.tapToClose = true;
                    options.tapToToggleControls = false;
                }
                break;
            }
        }
        if(disableAnimation) {
            options.showAnimationDuration = 0;
        }
        // Pass data to PhotoSwipe and initialize it
        gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
        // see: http://photoswipe.com/documentation/responsive-images.html
        var realViewportWidth,
            useLargeImages = false,
            firstResize = true,
            imageSrcWillChange;
        gallery.listen('beforeResize', function() {
            var dpiRatio = window.devicePixelRatio ? window.devicePixelRatio : 1;
            dpiRatio = Math.min(dpiRatio, 2.5);
            realViewportWidth = gallery.viewportSize.x * dpiRatio;
            if(realViewportWidth >= 1200 || (!gallery.likelyTouchDevice && realViewportWidth > 800) || screen.width > 1200 ) {
                if(!useLargeImages) {
                    useLargeImages = true;
                    imageSrcWillChange = true;
                }
            } else {
                if(useLargeImages) {
                    useLargeImages = false;
                    imageSrcWillChange = true;
                }
            }
            if(imageSrcWillChange && !firstResize) {
                gallery.invalidateCurrItems();
            }
            if(firstResize) {
                firstResize = false;
            }
            imageSrcWillChange = false;
        });
        gallery.listen('gettingData', function(index, item) {
            if( useLargeImages ) {
                item.src = item.o.src;
                item.w = item.o.w;
                item.h = item.o.h;
            } else {
                item.src = item.m.src;
                item.w = item.m.w;
                item.h = item.m.h;
            }
        });
        gallery.init();
    };
    // select all gallery elements
    var galleryElements = document.querySelectorAll( gallerySelector );
    for(var i = 0, l = galleryElements.length; i < l; i++) {
        galleryElements[i].setAttribute('data-pswp-uid', i+1);
        galleryElements[i].onclick = onThumbnailsClick;
    }
    // Parse URL and open gallery if it contains #&pid=3&gid=1
    var hashData = photoswipeParseHash();
    if(hashData.pid && hashData.gid) {
        openPhotoSwipe( hashData.pid,  galleryElements[ hashData.gid - 1 ], true, true );
    }
};

/**
 * floatTool 包含加减乘除四个方法，能确保浮点数运算不丢失精度
 *
 * 我们知道计算机编程语言里浮点数计算会存在精度丢失问题（或称舍入误差），其根本原因是二进制和实现位数限制有些数无法有限表示
 * 以下是十进制小数对应的二进制表示
 *      0.1 >> 0.0001 1001 1001 1001…（1001无限循环）
 *      0.2 >> 0.0011 0011 0011 0011…（0011无限循环）
 * 计算机里每种数据类型的存储是一个有限宽度，比如 JavaScript 使用 64 位存储数字类型，因此超出的会舍去。舍去的部分就是精度丢失的部分。
 *
 * ** method **
 *  add / subtract / multiply /divide
 *
 * ** explame **
 *  0.1 + 0.2 == 0.30000000000000004 （多了 0.00000000000004）
 *  0.2 + 0.4 == 0.6000000000000001  （多了 0.0000000000001）
 *  19.9 * 100 == 1989.9999999999998 （少了 0.0000000000002）
 *
 * floatObj.add(0.1, 0.2) >> 0.3
 * floatObj.multiply(19.9, 100) >> 1990
 *
 */
var floatTool = function() {

    /*
     * 判断obj是否为一个整数
     */
    function isInteger(obj) {
        return Math.floor(obj) === obj
    }

    /*
     * 将一个浮点数转成整数，返回整数和倍数。如 3.14 >> 314，倍数是 100
     * @param floatNum {number} 小数
     * @return {object}
     *   {times:100, num: 314}
     */
    function toInteger(floatNum) {
        var ret = {times: 1, num: 0}
        if (isInteger(floatNum)) {
            ret.num = floatNum
            return ret
        }
        var strfi  = floatNum + ''
        var dotPos = strfi.indexOf('.')
        var len    = strfi.substr(dotPos+1).length
        var times  = Math.pow(10, len)
        var intNum = parseInt(floatNum * times + 0.5, 10)
        ret.times  = times
        ret.num    = intNum
        return ret
    }

    /*
     * 核心方法，实现加减乘除运算，确保不丢失精度
     * 思路：把小数放大为整数（乘），进行算术运算，再缩小为小数（除）
     *
     * @param a {number} 运算数1
     * @param b {number} 运算数2
     * @param digits {number} 精度，保留的小数点数，比如 2, 即保留为两位小数
     * @param op {string} 运算类型，有加减乘除（add/subtract/multiply/divide）
     *
     */
    function operation(a, b, op) {
        var o1 = toInteger(a)
        var o2 = toInteger(b)
        var n1 = o1.num
        var n2 = o2.num
        var t1 = o1.times
        var t2 = o2.times
        var max = t1 > t2 ? t1 : t2
        var result = null
        switch (op) {
            case 'add':
                if (t1 === t2) { // 两个小数位数相同
                    result = n1 + n2
                } else if (t1 > t2) { // o1 小数位 大于 o2
                    result = n1 + n2 * (t1 / t2)
                } else { // o1 小数位 小于 o2
                    result = n1 * (t2 / t1) + n2
                }
                return result / max
            case 'subtract':
                if (t1 === t2) {
                    result = n1 - n2
                } else if (t1 > t2) {
                    result = n1 - n2 * (t1 / t2)
                } else {
                    result = n1 * (t2 / t1) - n2
                }
                return result / max
            case 'multiply':
                result = (n1 * n2) / (t1 * t2)
                return result
            case 'divide':
                return result = function() {
                    var r1 = n1 / n2
                    var r2 = t2 / t1
                    return operation(r1, r2, 'multiply')
                }()
        }
    }

    // 加减乘除的四个接口
    function add(a, b) {
        return operation(a, b, 'add')
    }
    function subtract(a, b) {
        return operation(a, b, 'subtract')
    }
    function multiply(a, b) {
        return operation(a, b, 'multiply')
    }
    function divide(a, b) {
        return operation(a, b, 'divide')
    }

    // exports
    return {
        add: add,
        subtract: subtract,
        multiply: multiply,
        divide: divide
    }
}();