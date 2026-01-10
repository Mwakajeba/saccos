class UserSession {
  static UserSession? _instance;
  
  int? userId;
  String? name;
  String? phone;
  String? branch;
  int? groupId;
  String? groupName;
  String? memberNo;
  String? gender;
  String? photoUrl;
  List<dynamic>? loans;
  List<dynamic>? members;
  
  UserSession._();
  
  static UserSession get instance {
    _instance ??= UserSession._();
    print('=== USER SESSION DATA ===');
    print('userId: ${_instance!.userId}');
    print('name: ${_instance!.name}');
    print('phone: ${_instance!.phone}');
    print('branch: ${_instance!.branch}');
    print('groupId: ${_instance!.groupId}');
    print('groupName: ${_instance!.groupName}');
    print('memberNo: ${_instance!.memberNo}');
    print('gender: ${_instance!.gender}');
    print('loans count: ${_instance!.loans?.length ?? 0}');
    print('members count: ${_instance!.members?.length ?? 0}');
    print('========================');
    return _instance!;
  }
  
  void setUserData(Map<String, dynamic> data) {
    print('=== setUserData called ===');
    print('Keys in data: ${data.keys.toList()}');
    userId = data['user_id'];
    name = data['name'];
    phone = data['phone'];
    branch = data['branch'];
    groupId = data['group_id'];
    groupName = data['group_name'];
    memberNo = data['memberno'];
    gender = data['gender'];
    photoUrl = data['photo'];
    loans = data['loans'];
    members = data['members'];
    print('Saved: userId=$userId, name=$name, phone=$phone, photoUrl=$photoUrl, loans count=${loans?.length}');
    print('=========================');
  }
  
  void clear() {
    userId = null;
    name = null;
    phone = null;
    branch = null;
    groupId = null;
    groupName = null;
    memberNo = null;
    gender = null;
    photoUrl = null;
    loans = null;
    members = null;
  }
  
  bool get isLoggedIn => userId != null;
}
