﻿//------------------------------------------------------------------------------
// <auto-generated>
//     This code was generated by a tool.
//     Runtime Version:2.0.50727.1433
//
//     Changes to this file may cause incorrect behavior and will be lost if
//     the code is regenerated.
// </auto-generated>
//------------------------------------------------------------------------------

// 
// This source code was auto-generated by xsd, Version=2.0.50727.42.
// 
namespace Timekeeping.TimekeepingReqResp {
    using System.Xml.Serialization;
    
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class NOPRequest {
        
        private string valueField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlTextAttribute()]
        public string Value {
            get {
                return this.valueField;
            }
            set {
                this.valueField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class NOPResponse {
        
        private string valueField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlTextAttribute()]
        public string Value {
            get {
                return this.valueField;
            }
            set {
                this.valueField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class error {
        
        private string codeField;
        
        private string valueField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlAttributeAttribute()]
        public string code {
            get {
                return this.codeField;
            }
            set {
                this.codeField = value;
            }
        }
        
        /// <remarks/>
        [System.Xml.Serialization.XmlTextAttribute()]
        public string Value {
            get {
                return this.valueField;
            }
            set {
                this.valueField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class EnumerateTrackerUnitsRequest {
        
        private string userIdField;
        
        private string dateField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string userId {
            get {
                return this.userIdField;
            }
            set {
                this.userIdField = value;
            }
        }
        
        /// <remarks/>
        public string Date {
            get {
                return this.dateField;
            }
            set {
                this.dateField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class EnumerateTrackerUnitsResponse {
        
        private EnumerateTrackerUnitsResponseTrackerUnit[] trackerUnitsField;
        
        private error errorField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlArrayItemAttribute("TrackerUnit", IsNullable=false)]
        public EnumerateTrackerUnitsResponseTrackerUnit[] TrackerUnits {
            get {
                return this.trackerUnitsField;
            }
            set {
                this.trackerUnitsField = value;
            }
        }
        
        /// <remarks/>
        public error error {
            get {
                return this.errorField;
            }
            set {
                this.errorField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    public partial class EnumerateTrackerUnitsResponseTrackerUnit {
        
        private string userIDField;
        
        private string dateField;
        
        private string periodField;
        
        private string projectIDField;
        
        private string stateField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string UserID {
            get {
                return this.userIDField;
            }
            set {
                this.userIDField = value;
            }
        }
        
        /// <remarks/>
        public string Date {
            get {
                return this.dateField;
            }
            set {
                this.dateField = value;
            }
        }
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string Period {
            get {
                return this.periodField;
            }
            set {
                this.periodField = value;
            }
        }
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string ProjectID {
            get {
                return this.projectIDField;
            }
            set {
                this.projectIDField = value;
            }
        }
        
        /// <remarks/>
        public string State {
            get {
                return this.stateField;
            }
            set {
                this.stateField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class AuthenticateResponse {
        
        private AuthenticateResponseAuthenticationResult authenticationResultField;
        
        private error errorField;
        
        /// <remarks/>
        public AuthenticateResponseAuthenticationResult authenticationResult {
            get {
                return this.authenticationResultField;
            }
            set {
                this.authenticationResultField = value;
            }
        }
        
        /// <remarks/>
        public error error {
            get {
                return this.errorField;
            }
            set {
                this.errorField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    public enum AuthenticateResponseAuthenticationResult {
        
        /// <remarks/>
        Authenticated,
        
        /// <remarks/>
        Rejected,
        
        /// <remarks/>
        Error,
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class AuthenticateRequest {
        
        private string usernameField;
        
        private string passwordField;
        
        /// <remarks/>
        public string username {
            get {
                return this.usernameField;
            }
            set {
                this.usernameField = value;
            }
        }
        
        /// <remarks/>
        public string password {
            get {
                return this.passwordField;
            }
            set {
                this.passwordField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class UserIDRequest {
        
        private string usernameField;
        
        /// <remarks/>
        public string username {
            get {
                return this.usernameField;
            }
            set {
                this.usernameField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class UserIDResponse {
        
        private string userIdField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string userId {
            get {
                return this.userIdField;
            }
            set {
                this.userIdField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class TrackerUnitSubmitResponse {
        
        private error errorField;
        
        /// <remarks/>
        public error error {
            get {
                return this.errorField;
            }
            set {
                this.errorField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class TrackerUnitSubmitRequest {
        
        private string userIDField;
        
        private string dateField;
        
        private string periodField;
        
        private string projectIDField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string UserID {
            get {
                return this.userIDField;
            }
            set {
                this.userIDField = value;
            }
        }
        
        /// <remarks/>
        public string Date {
            get {
                return this.dateField;
            }
            set {
                this.dateField = value;
            }
        }
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string Period {
            get {
                return this.periodField;
            }
            set {
                this.periodField = value;
            }
        }
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string ProjectID {
            get {
                return this.projectIDField;
            }
            set {
                this.projectIDField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class TrackerUnitRangeSubmitRequest {
        
        private string userIDField;
        
        private string dateField;
        
        private string startPeriodField;
        
        private string endPeriodField;
        
        private string projectIDField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string UserID {
            get {
                return this.userIDField;
            }
            set {
                this.userIDField = value;
            }
        }
        
        /// <remarks/>
        public string Date {
            get {
                return this.dateField;
            }
            set {
                this.dateField = value;
            }
        }
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string StartPeriod {
            get {
                return this.startPeriodField;
            }
            set {
                this.startPeriodField = value;
            }
        }
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string EndPeriod {
            get {
                return this.endPeriodField;
            }
            set {
                this.endPeriodField = value;
            }
        }
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string ProjectID {
            get {
                return this.projectIDField;
            }
            set {
                this.projectIDField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class TrackerUnitRangeSubmitResponse {
        
        private error errorField;
        
        /// <remarks/>
        public error error {
            get {
                return this.errorField;
            }
            set {
                this.errorField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class EnumerateProjectsRequest {
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class EnumerateProjectsResponse {
        
        private EnumerateProjectsResponseProjectCode[] projectCodesField;
        
        private error errorField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlArrayItemAttribute("ProjectCode", IsNullable=false)]
        public EnumerateProjectsResponseProjectCode[] ProjectCodes {
            get {
                return this.projectCodesField;
            }
            set {
                this.projectCodesField = value;
            }
        }
        
        /// <remarks/>
        public error error {
            get {
                return this.errorField;
            }
            set {
                this.errorField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    public partial class EnumerateProjectsResponseProjectCode {
        
        private string projectIDField;
        
        private string projectCodeField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string ProjectID {
            get {
                return this.projectIDField;
            }
            set {
                this.projectIDField = value;
            }
        }
        
        /// <remarks/>
        public string ProjectCode {
            get {
                return this.projectCodeField;
            }
            set {
                this.projectCodeField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class SubmitDayResponse {
        
        private error errorField;
        
        /// <remarks/>
        public error error {
            get {
                return this.errorField;
            }
            set {
                this.errorField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class SubmitDayRequest {
        
        private string userIDField;
        
        private string dateField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string UserID {
            get {
                return this.userIDField;
            }
            set {
                this.userIDField = value;
            }
        }
        
        /// <remarks/>
        public string Date {
            get {
                return this.dateField;
            }
            set {
                this.dateField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class RetractDayRequest {
        
        private string userIDField;
        
        private string dateField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string UserID {
            get {
                return this.userIDField;
            }
            set {
                this.userIDField = value;
            }
        }
        
        /// <remarks/>
        public string Date {
            get {
                return this.dateField;
            }
            set {
                this.dateField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class RetractDayResponse {
        
        private error errorField;
        
        /// <remarks/>
        public error error {
            get {
                return this.errorField;
            }
            set {
                this.errorField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class IsDaySubmittedRequest {
        
        private string userIDField;
        
        private string dateField;
        
        /// <remarks/>
        [System.Xml.Serialization.XmlElementAttribute(DataType="nonNegativeInteger")]
        public string UserID {
            get {
                return this.userIDField;
            }
            set {
                this.userIDField = value;
            }
        }
        
        /// <remarks/>
        public string Date {
            get {
                return this.dateField;
            }
            set {
                this.dateField = value;
            }
        }
    }
    
    /// <remarks/>
    [System.CodeDom.Compiler.GeneratedCodeAttribute("xsd", "2.0.50727.42")]
    [System.SerializableAttribute()]
    [System.Diagnostics.DebuggerStepThroughAttribute()]
    [System.ComponentModel.DesignerCategoryAttribute("code")]
    [System.Xml.Serialization.XmlTypeAttribute(AnonymousType=true, Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd")]
    [System.Xml.Serialization.XmlRootAttribute(Namespace="http://www.quantumsignal.com/TimekeepingReqResp.xsd", IsNullable=false)]
    public partial class IsDaySubmittedResponse {
        
        private bool responseField;
        
        /// <remarks/>
        public bool Response {
            get {
                return this.responseField;
            }
            set {
                this.responseField = value;
            }
        }
    }
}
