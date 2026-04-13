import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';

import '../models/leave_request.dart';
import '../models/leave_type.dart';
import 'api_client.dart';
import 'offline_queue_service.dart';

class LeaveService {
  LeaveService(this._apiClient, this._offlineQueue);

  final ApiClient _apiClient;
  final OfflineQueueService _offlineQueue;

  Future<List<LeaveRequest>> list() async {
    final Response<dynamic> response =
        await _apiClient.get<dynamic>('/leave-requests');
    final body = response.data;

    List<dynamic> rawList;
    if (body is Map<String, dynamic> && body['data'] is List) {
      rawList = body['data'] as List<dynamic>;
    } else if (body is List) {
      rawList = body;
    } else {
      return [];
    }

    return rawList
        .whereType<Map<String, dynamic>>()
        .map(LeaveRequest.fromJson)
        .toList();
  }

  Future<List<LeaveType>> getLeaveTypes() async {
    final Response<dynamic> response =
        await _apiClient.get<dynamic>('/leave-types');
    final body = response.data;

    List<dynamic> rawList;
    if (body is List) {
      rawList = body;
    } else {
      return [];
    }

    return rawList
        .whereType<Map<String, dynamic>>()
        .map(LeaveType.fromJson)
        .toList();
  }

  Future<LeaveRequest> create({
    required String type,
    int? leaveTypeId,
    required DateTime dateFrom,
    required DateTime dateTo,
    String? timeFrom, // HH:mm format
    String? timeTo,   // HH:mm format
    required String reason,
    String? doctorNote,
    String? sickLetterPath,
    String? permitPhotoPath,
  }) async {
    final formatter = DateFormat('yyyy-MM-dd');
    
    // Use FormData for multipart upload if file is provided (sick letter or permit photo)
    dynamic requestData;
    final hasFile = (sickLetterPath != null && sickLetterPath.isNotEmpty) ||
                    (permitPhotoPath != null && permitPhotoPath.isNotEmpty);
    
    if (hasFile) {
      final formData = FormData();
      formData.fields.addAll([
        MapEntry('type', type),
        MapEntry('date_from', formatter.format(dateFrom)),
        MapEntry('date_to', formatter.format(dateTo)),
        MapEntry('reason', reason),
      ]);
      if (leaveTypeId != null) {
        formData.fields.add(MapEntry('leave_type_id', leaveTypeId.toString()));
      }
      if (timeFrom != null && timeFrom.isNotEmpty) {
        formData.fields.add(MapEntry('time_from', timeFrom));
      }
      if (timeTo != null && timeTo.isNotEmpty) {
        formData.fields.add(MapEntry('time_to', timeTo));
      }
      if (doctorNote != null && doctorNote.isNotEmpty) {
        formData.fields.add(MapEntry('doctor_note', doctorNote));
      }
      if (sickLetterPath != null && sickLetterPath.isNotEmpty) {
        formData.files.add(MapEntry(
          'sick_letter',
          await MultipartFile.fromFile(
            sickLetterPath,
            filename: sickLetterPath.split('/').last,
          ),
        ));
      }
      if (permitPhotoPath != null && permitPhotoPath.isNotEmpty) {
        formData.files.add(MapEntry(
          'permit_photo',
          await MultipartFile.fromFile(
            permitPhotoPath,
            filename: permitPhotoPath.split('/').last,
          ),
        ));
      }
      requestData = formData;
    } else {
      final data = <String, dynamic>{
        'type': type,
        'date_from': formatter.format(dateFrom),
        'date_to': formatter.format(dateTo),
        'reason': reason,
      };
      if (leaveTypeId != null) {
        data['leave_type_id'] = leaveTypeId;
      }
      if (timeFrom != null && timeFrom.isNotEmpty) {
        data['time_from'] = timeFrom;
      }
      if (timeTo != null && timeTo.isNotEmpty) {
        data['time_to'] = timeTo;
      }
      if (doctorNote != null && doctorNote.isNotEmpty) {
        data['doctor_note'] = doctorNote;
      }
      requestData = data;
    }

    try {
      final Response<Map<String, dynamic>> response =
          await _apiClient.post<Map<String, dynamic>>(
        '/leave-requests',
        data: requestData,
      );

      final body = response.data;
      if (body == null) {
        throw ApiException(
          'Respons kosong dari server.',
          statusCode: response.statusCode,
        );
      }

      await _offlineQueue.sync();
      return LeaveRequest.fromJson(body);
    } on ApiException catch (e) {
      if (_offlineQueue.isOfflineError(e)) {
        await _offlineQueue.enqueueLeaveRequest(
          type: type,
          dateFrom: formatter.format(dateFrom),
          dateTo: formatter.format(dateTo),
          reason: reason,
          doctorNote: doctorNote,
          leaveTypeId: leaveTypeId, // BUG FIX: was silently dropped before
        );

        return LeaveRequest(
          id: 0,
          userId: 0,
          type: type,
          leaveTypeId: leaveTypeId, // BUG FIX: was missing
          dateFrom: dateFrom,
          dateTo: dateTo,
          reason: reason,
          status: 'pending',
          doctorNote: doctorNote,
          createdAt: null,
          updatedAt: null,
        );
      }
      rethrow;
    }
  }

  Future<void> cancel(int id) async {
    await _apiClient.delete<dynamic>('/leave-requests/$id');
  }

  // Admin methods
  
  /// List all leave requests (admin only)
  /// Can filter by status: pending, approved, rejected, cancelled
  Future<List<LeaveRequest>> listAdmin({String? status}) async {
    final params = <String, dynamic>{};
    if (status != null && status.isNotEmpty) {
      params['status'] = status;
    }

    final Response<dynamic> response = await _apiClient.get<dynamic>(
      '/admin/leave-requests',
      queryParameters: params,
    );
    final body = response.data;

    List<dynamic> rawList;
    if (body is Map<String, dynamic> && body['data'] is List) {
      rawList = body['data'] as List<dynamic>;
    } else if (body is List) {
      rawList = body;
    } else {
      return [];
    }

    return rawList
        .whereType<Map<String, dynamic>>()
        .map(LeaveRequest.fromJson)
        .toList();
  }

  /// Approve a leave request (admin only)
  Future<LeaveRequest> approve(int id) async {
    final Response<Map<String, dynamic>> response =
        await _apiClient.post<Map<String, dynamic>>(
      '/admin/leave-requests/$id/approve',
    );
    
    final body = response.data;
    if (body == null || body['data'] == null) {
      throw ApiException(
        'Respons kosong dari server.',
        statusCode: response.statusCode,
      );
    }

    return LeaveRequest.fromJson(body['data'] as Map<String, dynamic>);
  }

  /// Reject a leave request (admin only)
  Future<LeaveRequest> reject(int id) async {
    final Response<Map<String, dynamic>> response =
        await _apiClient.post<Map<String, dynamic>>(
      '/admin/leave-requests/$id/reject',
    );
    
    final body = response.data;
    if (body == null || body['data'] == null) {
      throw ApiException(
        'Respons kosong dari server.',
        statusCode: response.statusCode,
      );
    }

    return LeaveRequest.fromJson(body['data'] as Map<String, dynamic>);
  }

  /// Set leave request to pending status (admin only)
  Future<LeaveRequest> setPending(int id) async {
    final Response<Map<String, dynamic>> response =
        await _apiClient.post<Map<String, dynamic>>(
      '/admin/leave-requests/$id/pending',
    );
    
    final body = response.data;
    if (body == null || body['data'] == null) {
      throw ApiException(
        'Respons kosong dari server.',
        statusCode: response.statusCode,
      );
    }

    return LeaveRequest.fromJson(body['data'] as Map<String, dynamic>);
  }
}

final leaveServiceProvider = Provider<LeaveService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final offlineQueue = ref.watch(offlineQueueServiceProvider);
  return LeaveService(apiClient, offlineQueue);
});
